<?php

namespace App\Http\Controllers;

use App\Models\OracleOrderReceivingHeaderInterface;
use App\Models\OracleOrderReceivingLineInterface;
use App\Models\OracleTransactionInterface;
use App\Services\OracleInterfaceService;
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

    public function pushMorInterface(Request $request){

        //validate request
        $request->validate([
            'dr_number' => ['required','integer'],
            'org_id' => ['required','integer'],
        ]);

        $this->processPushtoOrderRcvInterface('MOR', $request->toArray());

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

        $details = [
            'HEADER_INTERFACE_ID' => $this->nextHeader,
            'GROUP_ID' => $this->nextGroup,
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

        switch ($transactionType) {
            case 'MOR': case 'DOTR':
                $details['RECEIPT_SOURCE_CODE'] = 'INVENTORY';
                $details['AUTO_TRANSACT_CODE'] = 'DELIVER';
                $details['SHIPMENT_NUM'] = $data['dr_number'];
                $details['SHIP_TO_ORGANIZATION_ID'] = $data['org_id'];
                break;
            case 'SOR':
                $details['RECEIPT_SOURCE_CODE'] = 'CUSTOMER';
                $details['CUSTOMER_ID'] = $data['customer_id'];
                break;
            default:
                # code...
                break;
        }
        try {
            DB::beginTransaction();
            OracleOrderReceivingHeaderInterface::create($details);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
        }
    }

    private function processPushtoOrderRcvLineInterface($transactionType, $data=[]){

        $details = [
            'INTERFACE_TRANSACTION_ID' => $this->transaction,
            'HEADER_INTERFACE_ID' => $this->nextHeader,
            'GROUP_ID' => $this->nextGroup,
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
            'SUBINVENTORY' => $data['branch'],
            'SHIPMENT_NUM' => $data['dr_number'],
            'EXPECTED_RECEIPT_DATE' => $this->sysDate,
            'VALIDATION_FLAG' => 'Y'
        ];

        switch ($transactionType) {
            case 'MOR': case 'DOTR':
                $details['SHIPMENT_NUM'] = $data['dr_number'];
                $details['RECEIPT_SOURCE_CODE'] = 'INVENTORY';
                $details['SOURCE_DOCUMENT_CODE'] = 'INVENTORY';
                $details['SHIPMENT_HEADER_ID'] = $data['shipment_header_id'];
                $details['SHIPMENT_LINE_ID'] = $data['shipment_line_id'];
                break;
            case 'SOR':
                $details['OE_ORDER_HEADER_ID'] = $data['oe_order_header_id'];
                $details['OE_ORDER_LINE_ID']  = $data['oe_order_line_id'];
                $details['CUSTOMER_ID']  = $data['customer_id'];
                $details['CUSTOMER_SITE_ID']  = $data['customer_site_id'];
                $details['RECEIPT_SOURCE_CODE'] = 'CUSTOMER';
                $details['SOURCE_DOCUMENT_CODE'] = 'RMA';
                break;
            default:
                # code...
                break;
        }

        try {
            DB::beginTransaction();
            OracleOrderReceivingLineInterface::create($details);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
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
            'SUBINVENTORY_CODE' => $data['from_subinventory'], //'STAGINGMO',
            'TRANSACTION_UOM' => 'Pc',
            'TRANSACTION_DATE' => $this->sysDate,
            'TRANSFER_ORGANIZATION' => $data['transfer_org_id'], //223,263,224
            'TRANSFER_SUBINVENTORY' => $data['transfer_subinventory'],
            'TRANSACTION_MODE' => 3,
        ];

        switch ($transactionType) {
            case 'DOT':
                $details['SOURCE_CODE'] = 'BEAPOSMW';
                $details['REVISION'] = '';
                $details['TRANSACTION_TYPE_ID'] = 21;
                $details['TRANSACTION_QUANTITY'] = ($data['quantity'])*(-1);
                $details['LOCATOR_ID'] = $data['locator_id'];
                $details['SHIPMENT_NUMBER'] = $data['dr_number'];
            break;
            case 'MOR':
                $details['SOURCE_CODE'] = 'MIDDLEWARE';
                $details['REVISION'] = '';
                $details['TRANSACTION_TYPE_ID'] = 21;
                $details['TRANSACTION_QUANTITY'] = $data['quantity'];
                $details['REASON_ID'] = $data['reason_id'];
                $details['SHIPMENT_NUMBER'] = $data['dr_number'];
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
                $details['TRANSACTION_REFERENCE'] = $$data['dr_number'];
                $details['TRANSACTION_SOURCE_NAME'] = $$data['dr_number'];
            break;

            default:
                # code...
                break;
        }

        try {
            DB::beginTransaction();
            OracleTransactionInterface::create($details);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
        }
    }

}
