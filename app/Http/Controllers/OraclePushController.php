<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\DeliveryLine;
use App\Models\OracleDual;
use App\Models\OracleOrderReceivingHeaderInterface;
use App\Models\OracleOrderReceivingLineInterface;
use App\Models\OracleShipmentHeader;
use App\Models\OracleShipmentLine;
use App\Models\OracleTransactionInterface;
use App\Models\OrderStatus;
use App\Models\Pullout;
use App\Models\PulloutLine;
use App\Services\DeliveryInterfaceService;
use App\Services\OracleInterfaceService;
use App\Services\PulloutInterfaceService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OraclePushController extends Controller
{
    protected $sysDate;
    protected $nextHeader;
    protected $transaction;
    protected $matertialTrx;
    protected $nextRcv;
    protected $nextGroup;

    public function __construct(OracleInterfaceService $oracleService) {
        $this->sysDate = $oracleService->getSysdate();
        $this->nextHeader = $oracleService->getHeaderNextValue();
        $this->transaction = $oracleService->getTransactionNextValue();
        $this->nextRcv = $oracleService->getHeaderNextValue();
        $this->nextGroup = $oracleService->getGroupNextValue();
        $this->matertialTrx = $oracleService->getMaterialTransactionNextValue();
    }

    public function pushDotInterface(DeliveryInterfaceService $deliveryInterface){
        foreach ($deliveryInterface->getProcessingDeliveryLines() ?? [] as $value) {
            $this->processTransferInterface('DOT', $value);
            //update interface flag
            $drLine = DeliveryLine::find($value['line_id']);
            $drLine->interface_flag = 1;
            $drLine->save();
        }

        //update interface flag to headers
        foreach ($deliveryInterface->getProcessingDelivery() ?? [] as $value) {
            $drHead = Delivery::where('order_number', $value['order_number'])->first();
            $drHead->interface_flag = 1;
            $drHead->save();
        }
    }

    public function pushDotrInterface(DeliveryInterfaceService $deliveryInterface){
        foreach ($deliveryInterface->getProcessingDotrDelivery() ?? [] as $value) {
            //push to header interface
            $headerInterface = $this->processPushtoOrderRcvInterface('DOTR', $value);

            $shipment = OracleShipmentHeader::query()->getShipmentByRef($value['dr_number']);
            $headerId = ($shipment->getModel()->exists) ? $shipment->shipment_header_id : null;
            $lines = OracleShipmentLine::getShipmentById($headerId)->toArray();

            foreach ($lines as $valueLine) {
                //push to line interface
                $dataPushLine = array_merge($valueLine, $value, $headerInterface);
                $this->processPushtoOrderRcvLineInterface('DOTR', $dataPushLine);
            }

            $drInterfaced = Delivery::where('order_number', $value['order_number'])->first();
            $drInterfaced->update([
                'status' => OrderStatus::PROCESSING_DOTR,
                'shipment_header_id' => $headerId,
                'interface_flag' => 1
            ]);

            $drInterfaced->lines()->update([
                'interface_flag' => 1
            ]);
        }
    }

    public function pushSitInterface(DeliveryInterfaceService $deliveryInterface){
        foreach ($deliveryInterface->getProcessingSitLines() ?? [] as $value) {
            $this->processTransferInterface('SIT', $value);

            //update interface flag
            $drLine = DeliveryLine::find($value['line_id']);
            $drLine->interface_flag = 1;
            $drLine->save();
        }

        //update interface flag to headers
        foreach ($deliveryInterface->getProcessingSit() ?? [] as $value) {
            $drHead = Delivery::where('order_number', $value['order_number'])->first();
            $drHead->interface_flag = 1;
            $drHead->save();
        }
    }

    public function pushMorInterface(PulloutInterfaceService $pulloutInterface){
        foreach ($pulloutInterface->getPendingLines() ?? [] as $value) {
            if($value['to_wh'] == 'DIGITS'){
                $value['transfer_org_id'] = 224;
            }
            if($value['to_wh'] == 'RMA'){
                $value['transfer_org_id'] = 225;
            }
            if($value['from_subinventory'] == 'ECOM'){
                $value['transfer_org_id'] = 263;
            }

            $this->processTransferInterface('MOR', $value);
            //update interface flag
            $pulloutLine = PulloutLine::find($value['line_id']);
            $pulloutLine->interface_flag = 1;
            $pulloutLine->save();
        }

        //update interface flag to headers
        foreach ($pulloutInterface->getPending() ?? [] as $value) {
            $pulloutHead = Pullout::where('document_number', $value['document_number'])->first();
            $pulloutHead->status = Pullout::PROCESSING;
            $pulloutHead->interface_flag = 1;
            $pulloutHead->save();
        }
    }


    public function pushMorLinesInterface(Request $request){

        //validate request
        $request->validate([
            'dr_number' => ['required','integer'],
            'org_id' => ['required','integer'],
            'quantity' => ['required','integer'],
            'item_id' => ['required','integer'],
            'shipment_header_id' => ['required','integer'],
            'shipment_line_id' => ['required','integer'],
            'branch' => ['required']
        ]);

        $this->processPushtoOrderRcvLineInterface('MOR', $request->toArray());
    }

    public function pushSorHeaderInterface(Request $request){
        //validate request
        $request->validate([
            'customer_id' => ['required','integer'],
        ]);

        $this->processPushtoOrderRcvInterface('SOR', $request->toArray());
    }

    public function pushSorLineInterface(Request $request){
        //validate request
        $request->validate([
            'quantity' => ['required','integer'],
            'item_id' => ['required','integer'],
            'org_id' => ['required','integer'],
            'branch' => ['required','string'],
            'dr_number' => ['required','string'],
            'customer_id' => ['required','integer'],
            'oe_order_header_id' => ['required','integer'],
            'oe_order_line_id' => ['required','integer'],
            'customer_site_id' => ['required','integer']
        ]);

        $this->processPushtoOrderRcvLineInterface('SOR', $request->toArray());
    }

    private function processPushtoOrderRcvInterface($transactionType, $data=[]){
        $interfaceHeaders = new OracleInterfaceService();
        $headerInterfaceId = $interfaceHeaders->getHeaderNextValue();
        $groupInterfaceId = $interfaceHeaders->getGroupNextValue();

        $details = [
            'HEADER_INTERFACE_ID' => $headerInterfaceId,
            'GROUP_ID' => $groupInterfaceId,
            'PROCESSING_STATUS_CODE' => 'PENDING',
            'TRANSACTION_TYPE' => 'NEW',
            'LAST_UPDATE_DATE' => $this->sysDate,
            'LAST_UPDATED_BY' => 0,
            'LAST_UPDATE_LOGIN' => 0,
            'CREATION_DATE' => $this->sysDate,
            'CREATED_BY' => 0,
            'EXPECTED_RECEIPT_DATE' => $this->sysDate,
            'VALIDATION_FLAG' => 'Y'
        ];

        $ref = [];
        $returnValue = [];
        $returnValue['header_interface_id'] = $headerInterfaceId;
        $returnValue['group_id'] = $groupInterfaceId;

        switch ($transactionType) {
            case 'MOR': case 'DOTR':
                $details['RECEIPT_SOURCE_CODE'] = 'INVENTORY';
                $details['AUTO_TRANSACT_CODE'] = 'DELIVER';
                $details['SHIPMENT_NUM'] = $data['dr_number'];
                $details['SHIP_TO_ORGANIZATION_ID'] = $data['org_id'];
                $ref['SHIPMENT_NUM'] = $data['dr_number'];
                break;
            case 'SOR':
                $details['RECEIPT_SOURCE_CODE'] = 'CUSTOMER';
                $details['CUSTOMER_ID'] = $data['customer_id'];
                $ref['HEADER_INTERFACE_ID'] = $headerInterfaceId;
                break;
            default:
                # code...
                break;
        }

        try {
            DB::connection('oracle')->beginTransaction();
            OracleOrderReceivingHeaderInterface::updateOrInsert($ref, $details);
            DB::connection('oracle')->commit();
        } catch (Exception $e) {
            DB::connection('oracle')->rollBack();
            Log::error($e->getMessage());
        }

        return $returnValue;
    }

    private function processPushtoOrderRcvLineInterface($transactionType, $data=[]){
        $transactionId = OracleDual::getTransactionNextValue();
        $details = [
            'INTERFACE_TRANSACTION_ID' => $transactionId, //$this->transaction,
            'HEADER_INTERFACE_ID' => $data['header_interface_id'],
            'GROUP_ID' => $data['group_id'],
            'LAST_UPDATE_DATE' => $this->sysDate,
            'LAST_UPDATED_BY' => 0,
            'CREATION_DATE' => $this->sysDate,
            'CREATED_BY' => 0,
            'LAST_UPDATE_LOGIN' => 0,
            'TRANSACTION_TYPE' => 'RECEIVE',
            'TRANSACTION_DATE' => $this->sysDate,
            'PROCESSING_STATUS_CODE' => 'PENDING',
            'PROCESSING_MODE_CODE' => 'BATCH',
            'TRANSACTION_STATUS_CODE' => 'PENDING',
            'QUANTITY' => $data['quantity'],
            'UNIT_OF_MEASURE' => 'PIECE',
            'INTERFACE_SOURCE_CODE' => 'RCV',
            'ITEM_ID' => $data['item_id'],
            'EMPLOYEE_ID' => 0,
            'AUTO_TRANSACT_CODE' => 'DELIVER',
            'TO_ORGANIZATION_ID' => $data['org_id'],
            'DESTINATION_TYPE_CODE' => 'INVENTORY',
            'SUBINVENTORY' => $data['to_subinventory'],
            'SHIPMENT_NUM' => $data['dr_number'],
            'EXPECTED_RECEIPT_DATE' => $this->sysDate,
            'VALIDATION_FLAG' => 'Y'
        ];

        $ref = [];
        switch ($transactionType) {
            case 'MOR': case 'DOTR':
                $details['RECEIPT_SOURCE_CODE'] = 'INVENTORY';
                $details['SOURCE_DOCUMENT_CODE'] = 'INVENTORY';
                $details['SHIPMENT_HEADER_ID'] = $data['shipment_header_id'];
                $details['SHIPMENT_LINE_ID'] = $data['shipment_line_id'];
                $ref['SHIPMENT_LINE_ID'] = $data['shipment_line_id'];
                $ref['SHIPMENT_NUM'] = $data['dr_number'];
                break;
            case 'SOR':
                $details['OE_ORDER_HEADER_ID'] = $data['oe_order_header_id'];
                $details['OE_ORDER_LINE_ID']  = $data['oe_order_line_id'];
                $details['CUSTOMER_ID']  = $data['customer_id'];
                $details['CUSTOMER_SITE_ID']  = $data['customer_site_id'];
                $details['RECEIPT_SOURCE_CODE'] = 'CUSTOMER';
                $details['SOURCE_DOCUMENT_CODE'] = 'RMA';
                $ref['OE_ORDER_LINE_ID']  = $data['oe_order_line_id'];
                break;
            default:
                # code...
                break;
        }

        try {
            DB::connection('oracle')->beginTransaction();
            OracleOrderReceivingLineInterface::updateOrInsert($ref, $details);
            DB::connection('oracle')->commit();
        } catch (Exception $e) {
            DB::connection('oracle')->rollBack();
            Log::error($e->getMessage());
        }
    }

    private function processTransferInterface($transactionType, $data=[]){
        $details = [
            'CREATION_DATE' => $this->sysDate,
            'CREATED_BY' => 0,
            'LAST_UPDATE_DATE' => $this->sysDate,
            'LAST_UPDATED_BY' => 0,
            'SOURCE_LINE_ID' => 1,
            'SOURCE_HEADER_ID' => 1,
            'PROCESS_FLAG' => 1,
            'INVENTORY_ITEM_ID' => $data['item_id'],
            'ORGANIZATION_ID' => $data['org_id'],
            'SUBINVENTORY_CODE' => $data['from_subinventory'],
            'TRANSACTION_UOM' => 'Pc',
            'TRANSACTION_DATE' => $this->sysDate,
            'TRANSFER_ORGANIZATION' => $data['transfer_org_id'],
            'TRANSFER_SUBINVENTORY' => $data['transfer_subinventory'],
            'TRANSACTION_MODE' => 3,
        ];

        $ref = [];

        switch ($transactionType) {
            case 'DOT':
                $details['SOURCE_CODE'] = 'BEAPOSMW';
                $details['REVISION'] = '';
                $details['TRANSACTION_TYPE_ID'] = 21;
                $details['TRANSACTION_QUANTITY'] = ($data['quantity'])*(-1);
                $details['LOCATOR_ID'] = $data['locator_id'];
                $details['SHIPMENT_NUMBER'] = $data['dr_number'];
                $ref['SHIPMENT_NUMBER'] = $data['dr_number'];
                $ref['INVENTORY_ITEM_ID'] = $data['item_id'];
            break;
            case 'MOR':
                $details['SOURCE_CODE'] = 'MIDDLEWARE';
                $details['REVISION'] = '';
                $details['TRANSACTION_TYPE_ID'] = 21;
                $details['TRANSACTION_QUANTITY'] = $data['quantity'];
                $details['REASON_ID'] = $data['reason_id'];
                $details['SHIPMENT_NUMBER'] = $data['document_number'];
                $ref['SHIPMENT_NUMBER'] = $data['document_number'];
            break;
            case 'SIT':
                $details['SOURCE_CODE'] = 'INV';
                $details['TRANSACTION_TYPE_ID'] = 2;
                $details['TRANSACTION_QUANTITY'] = $data['quantity'];
                $details['LOCATOR_ID'] = $data['locator_id'];
                $details['TRANSACTION_INTERFACE_ID'] = $this->matertialTrx;
                $details['TRANSACTION_HEADER_ID'] = $this->matertialTrx;
                $details['TRANSACTION_COST'] = 0;
                $details['LAST_UPDATE_LOGIN'] = 0;
                $details['TRANSACTION_SOURCE_TYPE_ID'] = 13;
                $details['TRANSACTION_ACTION_ID'] = 2;
                $details['LOCK_FLAG'] = 2;
                $details['FLOW_SCHEDULE'] = 'Y';
                $details['SCHEDULED_FLAG'] = 2;
                $details['TRANSACTION_REFERENCE'] = $data['dr_number'];
                $details['TRANSACTION_SOURCE_NAME'] = $data['dr_number'];
                $ref['TRANSACTION_REFERENCE'] = $data['dr_number'];
            break;

            default:
                # code...
                break;
        }
        try {
            DB::connection('oracle')->beginTransaction();
            OracleTransactionInterface::updateOrInsert($ref, $details);
            DB::connection('oracle')->commit();
        } catch (Exception $e) {
            DB::connection('oracle')->rollBack();
            Log::error($e->getMessage());
        }
    }

    public function acceptedDate($p_delivery_id)
    {
        try {
            DB::connection('oracle')->statement('BEGIN ACCEPTED_DATE_BPG(:p_delivery_id); END;',
                ['p_delivery_id' => $p_delivery_id]);
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    public function closeTrip($p_delivery_id)
    {
        try {
            DB::connection('oracle')->statement('BEGIN CLOSE_TRIP_BPG(:p_delivery_id); END;',
                ['p_delivery_id' => $p_delivery_id]);
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }

    }

}
