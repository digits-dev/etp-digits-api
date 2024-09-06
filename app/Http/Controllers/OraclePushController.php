<?php

namespace App\Http\Controllers;

use App\Models\OracleOrderReceivingHeaderInterface;
use App\Models\OracleOrderReceivingLineInterface;
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

    public function pushSorLineInterface(){

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

}
