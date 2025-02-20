<?php

namespace App\Helpers;

use App\Models\StorePullout;
use App\Models\StoreTransfer;
use Illuminate\Support\Facades\Session;
use crocodicstudio\crudbooster\helpers\CRUDBooster;
use App\Models\CmsPrivilege;

class Helper
{
    private const VIEWREPORT = [CmsPrivilege::SUPERADMIN, CmsPrivilege::AUDIT, CmsPrivilege::IC, CmsPrivilege::MERCH];
	private const VIEWREPORTLOGISTIC = [CmsPrivilege::LOGISTICS, CmsPrivilege::LOGISTICSTM];
	private const VIEWREPORTAPPROVER = [CmsPrivilege::APPROVER];
	private const VIEWREPORTWHRMA = [CmsPrivilege::RMA, CmsPrivilege::WHTM, CmsPrivilege::WHTL];
	private const VIEWREPORTWHDISTRI = [CmsPrivilege::DISTRIOPS];
	private const VIEWREPORTWHRTLFRAONL = [CmsPrivilege::RTLOPS, CmsPrivilege::FRAOPS];
	private const VIEWREPORTWHRTLFRAOPS = [CmsPrivilege::RTLFRAOPS];
	private const VIEWREPORTWHFRAVIEWER = [CmsPrivilege::FRAVIEWER];

    public static function myChannel(){
        return Session::get('channel_id');
    }

    public static function myStore(){
        return Session::get('store_id');
    }

    public static function myTransferGroup(){
        return Session::get('transfer_group');
    }

    public static function myApprovalStore(){
        return Session::get('approval_stores');
    }

    public static function myPosWarehouse(){
        return Session::get('pos_warehouse');
    }

    public static function getTotalPendingList(){
        return self::getPendingSTR() + self::getPendingSTW() + self::getPendingSTS();
    }

    public static function getPendingSTW(){
        if(CRUDBooster::isSuperAdmin()){
            return StorePullout::pending()->stw()->count();
        }else{
            return StorePullout::pending()->whereIn('stores_id',self::myApprovalStore())->stw()->count();
        }
    }

    public static function getPendingSTR(){
        if(CRUDBooster::isSuperAdmin()){
            return StorePullout::pending()->str()->count();
        }else{
            return StorePullout::pending()->whereIn('stores_id',self::myApprovalStore())->str()->count();
        }
    }

    public static function getPendingSTS(){
        if(CRUDBooster::isSuperAdmin()){
            return StoreTransfer::confirmed()->count();
        }else{
            return StoreTransfer::confirmed()->whereIn('stores_id',self::myApprovalStore())->count();
        }
    }

    public static function getConfimationSTS(){
        if(CRUDBooster::isSuperAdmin()){
            return StoreTransfer::forconfirmation()->count();
        }else{
            return StoreTransfer::forconfirmation()->where('stores_id_destination', self::myStore())->count();
        }
    }

    public static function generateStsParams() {
		$query_filter_params = [];
        if (in_array(CRUDBooster::myPrivilegeId(), self::VIEWREPORT)) {
			//do nothing
		} elseif (in_array(CRUDBooster::myPrivilegeId(), self::VIEWREPORTLOGISTIC)) {
			$query_filter_params[] = [
				'method' => 'where',
				'params' => ['store_transfers.transport_types_id', 1]
			];
		} elseif (in_array(CRUDBooster::myPrivilegeId(), self::VIEWREPORTAPPROVER)) {
			$query_filter_params[] = [
				'method' => 'whereIn',
				'params' => ['store_transfers.stores_id', self::myApprovalStore()]
			];
		} elseif (in_array(CRUDBooster::myPrivilegeId(), self::VIEWREPORTWHRMA)) {
			$query_filter_params[] = [
				'method' => 'where',
				'params' => ['store_transfers.wh_to', self::myPosWarehouse()]
			];
		} elseif (in_array(CRUDBooster::myPrivilegeId(), self::VIEWREPORTWHDISTRI)) {
			$query_filter_params[] = [
				'method' => 'nested',
				'params' => [
					[
						'method' => 'whereIn',
						'params' => ['store_transfers.channels_id', [6, 7, 10, 11]]
					],
					[
						'method' => 'orWhereIn',
						'params' => ['store_transfers.reasons_id', ['173', 'R-12']]
					]
				]
			];
		} elseif (in_array(CRUDBooster::myPrivilegeId(), self::VIEWREPORTWHRTLFRAONL)) {
			if (!self::myStore()) {
				$query_filter_params[] = [
					'method' => 'where',
					'params' => ['store_transfers.channels_id', self::myChannel()]
				];
			} else {
				$query_filter_params[] = [
					'method' => 'where',
					'params' => ['store_transfers.channels_id', self::myChannel()]
				];
				$query_filter_params[] = [
					'method' => 'whereIn',
					'params' => ['store_transfers.stores_id', self::myStore()]
				];
			}
		} elseif (in_array(CRUDBooster::myPrivilegeId(), self::VIEWREPORTWHRTLFRAOPS)) {
			$query_filter_params[] = [
				'method' => 'whereIn',
				'params' => ['store_transfers.channels_id', [1, 2]]
			];
		} elseif (in_array(CRUDBooster::myPrivilegeId(), self::VIEWREPORTWHFRAVIEWER)) {
			$query_filter_params[] = [
				'method' => 'whereIn',
				'params' => ['store_transfers.stores_id', self::myStore()]
			];
		} else {
			$query_filter_params[] = [
				'method' => 'where',
				'params' => ['store_transfers.stores_id', self::myStore()]
			];
            $query_filter_params[] = [
				'method' => 'orWhere',
				'params' => ['store_transfers.stores_id_destination', self::myStore()]
			];
		}

		return $query_filter_params;
	}

    public static function generatePulloutParams() {
		$query_filter_params = [];

        if (in_array(CRUDBooster::myPrivilegeId(), self::VIEWREPORT)) {
			//do nothing
		} elseif (in_array(CRUDBooster::myPrivilegeId(), self::VIEWREPORTLOGISTIC)) {
			$query_filter_params[] = [
				'method' => 'where',
				'params' => ['store_pullouts.transport_types_id', 1]
			];
		} elseif (in_array(CRUDBooster::myPrivilegeId(), self::VIEWREPORTAPPROVER)) {
			$query_filter_params[] = [
				'method' => 'whereIn',
				'params' => ['store_pullouts.stores_id', self::myApprovalStore()]
			];
		} elseif (in_array(CRUDBooster::myPrivilegeId(), self::VIEWREPORTWHRMA)) {
			$query_filter_params[] = [
				'method' => 'where',
				'params' => ['store_pullouts.wh_to', self::myPosWarehouse()]
			];
		} elseif (in_array(CRUDBooster::myPrivilegeId(), self::VIEWREPORTWHDISTRI)) {
			$query_filter_params[] = [
				'method' => 'nested',
				'params' => [
					[
						'method' => 'whereIn',
						'params' => ['store_pullouts.channels_id', [6, 7, 10, 11]]
					],
					[
						'method' => 'orWhereIn',
						'params' => ['store_pullouts.reasons_id', ['173', 'R-12']]
					]
				]
			];
		} elseif (in_array(CRUDBooster::myPrivilegeId(), self::VIEWREPORTWHRTLFRAONL)) {
			if (!self::myStore()) {
				$query_filter_params[] = [
					'method' => 'where',
					'params' => ['store_pullouts.channels_id', self::myChannel()]
				];
			} else {
				$query_filter_params[] = [
					'method' => 'where',
					'params' => ['store_pullouts.channels_id', self::myChannel()]
				];
				$query_filter_params[] = [
					'method' => 'whereIn',
					'params' => ['store_pullouts.stores_id', self::myStore()]
				];
			}
		} elseif (in_array(CRUDBooster::myPrivilegeId(), self::VIEWREPORTWHRTLFRAOPS)) {
			$query_filter_params[] = [
				'method' => 'whereIn',
				'params' => ['store_pullouts.channels_id', [1, 2]]
			];
		} elseif (in_array(CRUDBooster::myPrivilegeId(), self::VIEWREPORTWHFRAVIEWER)) {
			$query_filter_params[] = [
				'method' => 'whereIn',
				'params' => ['store_pullouts.stores_id', self::myStore()]
			];
		} else {
			$query_filter_params[] = [
				'method' => 'where',
				'params' => ['store_pullouts.stores_id', self::myStore()]
			];
		}

		return $query_filter_params;
	}

    public static function generateDrParams() {
		$query_filter_params = [];
        if (in_array(CRUDBooster::myPrivilegeId(), self::VIEWREPORT)) {
			//do nothing
		}
        if (in_array(CRUDBooster::myPrivilegeId(), self::VIEWREPORTLOGISTIC)) {
			//do nothing
		}
		elseif (in_array(CRUDBooster::myPrivilegeId(), self::VIEWREPORTAPPROVER)) {
			$query_filter_params[] = [
				'method' => 'whereIn',
				'params' => ['deliveries.stores_id', self::myApprovalStore()]
			];
		} elseif (in_array(CRUDBooster::myPrivilegeId(), self::VIEWREPORTWHDISTRI)) {
			$query_filter_params[] = [
                'method' => 'whereIn',
                'params' => ['store_masters.channels_id', [6, 7, 10, 11]]
            ];
		} elseif (in_array(CRUDBooster::myPrivilegeId(), self::VIEWREPORTWHRTLFRAONL)) {
			if (!self::myStore()) {
				$query_filter_params[] = [
					'method' => 'where',
					'params' => ['store_masters.channels_id', self::myChannel()]
				];
			} else {
				$query_filter_params[] = [
					'method' => 'where',
					'params' => ['store_masters.channels_id', self::myChannel()]
				];
				$query_filter_params[] = [
					'method' => 'whereIn',
					'params' => ['store_masters.id', self::myStore()]
				];
			}
		} elseif (in_array(CRUDBooster::myPrivilegeId(), self::VIEWREPORTWHRTLFRAOPS)) {
			$query_filter_params[] = [
				'method' => 'whereIn',
				'params' => ['store_masters.channels_id', [1, 2]]
			];
		} elseif (in_array(CRUDBooster::myPrivilegeId(), self::VIEWREPORTWHFRAVIEWER)) {
			$query_filter_params[] = [
				'method' => 'whereIn',
				'params' => ['store_masters.id', self::myStore()]
			];
		} else {
			$query_filter_params[] = [
				'method' => 'where',
				'params' => ['store_masters.id', self::myStore()]
			];
		}

		return $query_filter_params;
	}
}
