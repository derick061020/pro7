<?php

namespace Modules\Pos\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Modules\Pos\Traits\CashReportTrait;
use App\Models\Tenant\PaymentMethodType;
use Mpdf\Mpdf;
use App\Models\Tenant\Cash;
use Carbon\Carbon;


class CashReportController extends Controller
{
    
    use CashReportTrait;

    
    /**
     * 
     * Reporte de caja para pagos en efectivo con destino caja, ingresos y egresos
     *
     * @param  int $cash_id
     */
    public function cashPaymentReportExcel($cash_id)
    {
        $cash = Cash::filterDataCashPaymentReport()->findOrFail($cash_id);
        $data = app(CashController::class)->getHeaderCommonDataToReport($cash);

        $this->setDataCashPaymentReportExcel($cash, $data);

        $filename = 'Reporte_pagos_efectivo_caja_'.date('YmdHis');

        return $this->generalExportReport($filename, 'pos::cash.reports.cash_payment_report_excel', ['data' => $data]);
    }

    
    /**
     * 
     * Reporte general de caja v2, asociado a pagos
     *
     * @param  int $cash_id
     */
    public function generalCashReportWithPayments($cash_id)
    {
        $cash = Cash::filterDataGeneralCashReport()->findOrFail($cash_id);

        $data = app(CashController::class)->getHeaderCommonDataToReport($cash);

        $filename = 'Reporte_general_caja_v2_'.date('YmdHis');

        return $this->generalToPrintReport(
            'pos::cash.reports.general_cash_report_payments_pdf', 
            'general_cash_report_payments',
            $filename, 
            $this->getDataCashReportWithPayments($cash, $data)
        );
    }

    
    /**
     *
     * Generar reporte de pagos asociados a caja, con destino caja y en efectivo
     * 
     * Disponible para cpe u nv
     *
     * @param  int $cash
     */
    public function reportPaymentsAssociatedCash($cash_id)
    {
        $cash = Cash::with([
                        'global_destination' => function($query){
                            return $query->filtersPaymentsAssociatedCash();
                        }
                    ])
                    ->findOrFail($cash_id);

        $data = app(CashController::class)->getHeaderCommonDataToReport($cash);

        $filename = 'Reporte_ingresos_caja_efectivo_'.date('YmdHis');

        return $this->generalToPrintReport(
            'pos::cash.reports.report_payments_associated_cash_pdf', 
            'report_payments_associated_cash',
            $filename, 
            $this->getDataPaymentsAssociatedCash($cash, $data)
        );
    }


    /**
     *
     * Generar reporte de Resumen de Operaciones Diarias
     *
     * @param  int $cash
     */
    public function reportSummaryDailyOperations($cash_id)
    {
        $cash = Cash::with(['cash_documents', 'cash_documents_credit'])->findOrFail($cash_id);
        $header_data = app(CashController::class)->getHeaderCommonDataToReport($cash);


        $data = $this->initDataSummaryDailyOperations();

        $this->setDataCreditSales($cash, $data);

        $this->setDataCashSalesPurchases($cash, $data);

        $this->calculateGlobalValues($data);
        $cashPayments = \App\Models\Tenant\Payment::where('payment_destination_id', $cash->id)->get();
        foreach($cashPayments as $cashPayment){
            if($cashPayment->payment_method_type_id == PaymentMethodType::CASH_PAYMENT_ID){
                $data['cash_sales_income']['total_cash'] += $cashPayment->amount;
                $data['total_cash_sales'] += $cashPayment->amount;
                $data['cash_balance'] += $cashPayment->amount;
            }else{
                $data['cash_sales_income']['total_transfer'] += $cashPayment->amount;
                $data['total_cash_purchases_transfer'] += $cashPayment->amount;
                $data['balance_transfer'] += $cashPayment->amount;
            }
            $data['cash_sales_income']['total'] += $cashPayment->amount;
            $data['total_balance'] += $cashPayment->amount;
            
        }

        $pdf_data = [
            'header_data' => $header_data,
            'data' => $data,
        ];

        $filename = 'Reporte_resumen_operaciones_diarias_'.date('YmdHis');

        return $this->generalToPrintReport('pos::cash.reports.report_summary_daily_operations_pdf', 'report_summary_daily_operations', $filename, $pdf_data);
    }

}
