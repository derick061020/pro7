<?php

namespace Modules\Pos\Traits;

use App\CoreFacturalo\Helpers\Functions\GeneralPdfHelper;
use Mpdf\Mpdf;
use App\Models\Tenant\PaymentMethodType;
use App\Models\Tenant\Document;
use App\Models\Tenant\SaleNote;
use App\Exports\GeneralFormatExport;


trait CashReportTrait
{
       
    /**
     *
     * Reporte excel v2 de caja para pagos en efectivo con destino caja, ingresos y egresos
     * 
     * @return void
     */
    public function setDataCashPaymentReportExcel($cash, &$data)
    {
        $payments = collect();

        foreach ($cash->global_destination as $global_payment) 
        {
            $payments->push($global_payment->payment->getDataCashPaymentReport());
        }
        foreach ($payments as $key => $payment) 
        {
            if($payment['type'] == 'sale_note'){
                $sale_note = SaleNote::find($payment['document_id']);
                if($sale_note && \App\Models\Tenant\Payment::where('payment_destination_id', $cash->id)->where('notes', $sale_note->id)->exists()){
                    $payments->forget($key);
                }
                if($sale_note->hotel_rent_id){
                    $payments->forget($key);
                }
            }elseif($payment['type'] == 'document'){
                $document = Document::find($payment['document_id']);
                if($document->hotel_rent_id){
                    $payments->forget($key);
                }
            }
        }
        foreach(\App\Models\Tenant\Payment::where('payment_destination_id', $cash->id)->get() as $payment){
            if($payment->payment_method_type_id != PaymentMethodType::CASH_PAYMENT_ID){
                continue;
            }
            $payments->push([
               "type"=>"advance",
               "type_transaction"=>"income",
               "type_transaction_description"=>"Venta",
               "date_of_issue"=>$payment->created_at,
               "number_full"=>$payment->id,
               "acquirer_name"=>$payment->customer['name'],
               "acquirer_number"=>$payment->customer['number'],
               "currency_type_id"=>'PEN',
               "document_id"=>$payment->document_id,
               "document_type_description"=>"AVANCE",
               "payment_method_type_id"=>$payment->payment_method_type_id,
               "payment"=>$payment->amount,
               "total"=>$payment->amount,
            ]);
        }

        $payments_pen = $payments->where('currency_type_id', PaymentMethodType::NATIONAL_CURRENCY_ID);
        $payments_usd = $payments->where('currency_type_id', PaymentMethodType::DOLAR_CURRENCY_ID);
        
        $data['payments_pen'] = $payments_pen;
        $data['payments_usd'] = $payments_usd;

        $data['cash_income_pen'] = GeneralPdfHelper::setNumberFormat($payments_pen->where('type_transaction', 'income')->sum('payment'));
        $data['cash_egress_pen'] = GeneralPdfHelper::setNumberFormat($payments_pen->where('type_transaction', 'egress')->sum('payment'));
        
        //saldo inicial de caja se considera en soles
        $data['balance_cash_pen'] = GeneralPdfHelper::setNumberFormat(($data['cash_income_pen'] + $data['cash_beginning_balance']) - $data['cash_egress_pen']); 


        $data['cash_income_usd'] = GeneralPdfHelper::setNumberFormat($payments_usd->where('type_transaction', 'income')->sum('payment'));
        $data['cash_egress_usd'] = GeneralPdfHelper::setNumberFormat($payments_usd->where('type_transaction', 'egress')->sum('payment'));

        $data['balance_cash_usd'] = GeneralPdfHelper::setNumberFormat($data['cash_income_usd'] - $data['cash_egress_usd']);

        $data['cash_beginning_balance'] = GeneralPdfHelper::setNumberFormat($data['cash_beginning_balance']);
    }


    /**
     *
     * Data para reporte de caja v2 asociados a caja
     * 
     * @return array
     */
    public function getDataCashReportWithPayments($cash, &$data)
    {
        $payments = collect();

        foreach ($cash->global_destination as $global_payment) 
        {
            $payments->push($global_payment->payment->getRowResourceCashPayment());
        }
        foreach ($payments as $key => $payment) 
        {
            if($payment['type'] == 'sale_note'){
                $sale_note = SaleNote::find($payment['document_id']);
                if($sale_note && \App\Models\Tenant\Payment::where('payment_destination_id', $cash->id)->where('notes', $sale_note->id)->exists()){
                    $payments->forget($key);
                }
                if($sale_note->hotel_rent_id){
                    $payments->forget($key);
                }
            }elseif($payment['type'] == 'document'){
                $document = Document::find($payment['document_id']);
                if($document->hotel_rent_id){
                    $payments->forget($key);
                }
            }
        }
        foreach(\App\Models\Tenant\Payment::where('payment_destination_id', $cash->id)->get() as $payment){
            $payments->push([
               "type"=>"advance",
               "type_transaction"=>"income",
               "type_transaction_description"=>"Venta",
               "date_of_issue"=>$payment->created_at,
               "number_full"=>$payment->id,
               "acquirer_name"=>$payment->customer['name'],
               "acquirer_number"=>$payment->customer['number'],
               "currency_type_id"=>'PEN',
               "document_id"=>$payment->document_id,
               "document_type_description"=>"AVANCE",
               "payment_method_type_id"=>$payment->payment_method_type_id,
               "payment"=>$payment->amount,
            ]);
        }

        $data['total_income'] = $payments->where('type_transaction', 'income')->where('payment_method_type_id', PaymentMethodType::CASH_PAYMENT_ID)->sum('payment');
        $data['total_egress'] = $payments->where('type_transaction', 'egress')->where('payment_method_type_id', PaymentMethodType::CASH_PAYMENT_ID)->sum('payment');
        $data['total_balance'] =  $data['cash_beginning_balance'] + $data['total_income'] - $data['total_egress'];

        $payments_with_payment_method = $payments->where('type', '!=', 'expense_payment');
        $expense_payments = $payments->where('type', 'expense_payment'); // no tiene relacion con payment_method_type_id, se agregara a la data de efectivo, ya que el registro va directo a caja
        
        // se agrupara pagos que tienen relacion con payment_method_type_id
        $group_payments = $payments_with_payment_method->sortBy('payment_method_type_id')->groupBy('payment_method_type_id');

        return [
            'data' => $data,
            'group_payments' => $group_payments,
            'expense_payments' => $expense_payments,
        ];
    }


    /**
     *
     * Data para reporte de pagos asociados a caja, con destino caja y en efectivo
     * 
     * @return array
     */
    public function getDataPaymentsAssociatedCash($cash, &$data)
    {
        $payments = collect();

        foreach ($cash->global_destination as $global_payment) 
        {
            $payments->push($global_payment->payment->getRowResourceCashPayment());
        }
        foreach ($payments as $key => $payment) 
        {
            if($payment['type'] == 'sale_note'){
                $sale_note = SaleNote::find($payment['document_id']);
                if($sale_note && \App\Models\Tenant\Payment::where('payment_destination_id', $cash->id)->where('notes', $sale_note->id)->exists()){
                    $payments->forget($key);
                }
                if($sale_note->hotel_rent_id){
                    $payments->forget($key);
                }
            }elseif($payment['type'] == 'document'){
                $document = Document::find($payment['document_id']);
                if($document->hotel_rent_id){
                    $payments->forget($key);
                }
            }
        }
        foreach(\App\Models\Tenant\Payment::where('payment_destination_id', $cash->id)->get() as $payment){
            if($payment->payment_method_type_id != PaymentMethodType::CASH_PAYMENT_ID){
                continue;
            }
            $payments->push([
               "type"=>"advance",
               "type_transaction"=>"income",
               "type_transaction_description"=>"Venta",
               "date_of_issue"=>$payment->created_at,
               "number_full"=>$payment->id,
               "acquirer_name"=>$payment->customer['name'],
               "acquirer_number"=>$payment->customer['number'],
               "currency_type_id"=>'PEN',
               "document_id"=>$payment->document_id,
               "document_type_description"=>"AVANCE",
               "payment_method_type_id"=>$payment->payment_method_type_id,
               "payment"=>$payment->amount,
            ]);
        }
        
        
        $data['total_income'] = $payments->sum('payment') ;

        return [
            'data' => $data,
            'payments' => $payments,
        ];
    }


    /**
     *
     * @return array
     */
    public function initDataSummaryDailyOperations()
    {
        return [
            'cash_sales_income' => [
                'total_cash' => 0,
                'total_transfer' => 0,
                'total' => 0,
            ],
            'credit_sales' => 0,
            'amortization_credit_sales' => [
                'total_cash' => 0,
                'total_transfer' => 0,
                'total' => 0,
            ],
            
            'purchase_cash' => [
                'total_cash' => 0,
                'total_transfer' => 0,
                'total' => 0,
            ],
            'credit_purchases' => 0,
            'amortization_credit_purchases' => [
                'total_cash' => 0,
                'total_transfer' => 0,
                'total' => 0,
            ],

            'total_cash_sales' => 0,
            'total_cash_purchases' => 0,

            'total_cash_sales_transfer' => 0,
            'total_cash_purchases_transfer' => 0,

            'cash_balance' => 0,
            'balance_transfer' => 0,
            'total_balance' => 0,
        ];
    }

    
    /**
     * 
     * Asignar datos de ventas al credito y amortizacion de ventas credito
     *
     * @param  Cash $cash
     * @param  array $data
     * @return void
     */
    public function setDataCreditSales($cash, &$data)
    {
        // para facturas y notas de venta
        foreach ($cash->cash_documents_credit as $cash_document_credit)
        {
            if($cash_document_credit->isPending())
            {
                $model_associated = $cash_document_credit->getDataModelAssociated();

                if($model_associated)
                {
                    $data_summary_daily = $model_associated->applySummaryDailyOperations();
                    
                    if($data_summary_daily['apply'])
                    {
                        $data['credit_sales'] += $model_associated->total;
                        
                        $total_cash = $model_associated->totalCashPaymentsWithoutDestination();
                        $total_transfer = $model_associated->totalTransferPayments();

                        $data['amortization_credit_sales']['total_cash'] += $total_cash;
                        $data['amortization_credit_sales']['total_transfer'] += $total_transfer;
                        $data['amortization_credit_sales']['total'] += $total_cash + $total_transfer;
                    }
                }
            }
        }
    }
    
    
    /**
     * 
     * Datos de ventas al contado efectivo/transferencia - Compras credito/contado
     *
     * @param  Cash $cash
     * @param  array $data
     * @return void
     */
    public function setDataCashSalesPurchases($cash, &$data)
    {
        foreach ($cash->cash_documents as $cash_document)
        {
            $model_associated = $cash_document->getDataModelAssociated();
            
            if($cash_document->sale_note && \App\Models\Tenant\Payment::where('payment_destination_id', $cash->id)->where('notes', $cash_document->sale_note->id)->exists()){
                continue;
            }
            if($model_associated->hotel_rent_id){
                continue;
            }
            
            if($model_associated)
            {
                $data_summary_daily = $model_associated->applySummaryDailyOperations();

                if($data_summary_daily['apply'])
                {
                    if($data_summary_daily['transaction_type'] === 'income')
                    {
                        $this->setDataCashSalesIncome($model_associated, $data);
                    }
                    else if($data_summary_daily['transaction_type'] === 'egress')
                    {
                        if($data_summary_daily['document_type'] === 'purchases') $this->setDataCashCreditPurchases($model_associated, $data);
                    }
                }
            }
        }
    }
    
        
    /**
     * 
     * Asignar valores finales
     *
     * @param  array $data
     * @return void
     */
    public function calculateGlobalValues(&$data)
    {
        // total de ingresos al contado en efectivo
        $data['total_cash_sales'] = $data['cash_sales_income']['total_cash'] + $data['amortization_credit_sales']['total_cash'];

        // total compras al contado en efectivo
        $data['total_cash_purchases'] = $data['purchase_cash']['total_cash'] + $data['amortization_credit_purchases']['total_cash'];

        // total ingresos al contado por transferencia
        $data['total_cash_sales_transfer'] = $data['cash_sales_income']['total_transfer'] + $data['amortization_credit_sales']['total_transfer'];

        // total compras al contado por transferencia
        $data['total_cash_purchases_transfer'] = $data['purchase_cash']['total_transfer'] + $data['amortization_credit_purchases']['total_transfer'];


        // saldo en efectivo
        $data['cash_balance'] = $data['total_cash_sales'] - $data['total_cash_purchases'];

        // saldo por transferencias
        $data['balance_transfer'] = $data['total_cash_sales_transfer'] - $data['total_cash_purchases_transfer'];

        // saldo total
        $data['total_balance'] = $data['cash_balance'] + $data['balance_transfer'];
        
    }


    /**
     * 
     * Datos de ventas al contado efectivo/transferencia
     *
     * @param  $model_associated
     * @param  array $data
     * @return void
     */
    public function setDataCashSalesIncome($model_associated, &$data)
    {
        $total_cash = $model_associated->totalCashPaymentsWithoutDestination();
        $total_transfer = $model_associated->totalTransferPayments();
        $total_cash_transfer = $total_cash + $total_transfer;

        $data['cash_sales_income']['total_cash'] += $total_cash;
        $data['cash_sales_income']['total_transfer'] += $total_transfer;
        $data['cash_sales_income']['total'] += $total_cash_transfer;
    }


    /**
     * 
     * Datos de compras al contado efectivo/transferencia y credito
     *
     * @param  $model_associated
     * @param  array $data
     * @return void
     */
    public function setDataCashCreditPurchases($model_associated, &$data)
    {
        $total_cash = $model_associated->totalCashPaymentsWithoutDestination();
        $total_transfer = $model_associated->totalTransferPayments();
        $total_cash_transfer = $total_cash + $total_transfer;

        //es una compra por pagar
        if($model_associated->isToPay())
        {
            $data['credit_purchases'] += $model_associated->total;

            $data['amortization_credit_purchases']['total_cash'] += $total_cash;
            $data['amortization_credit_purchases']['total_transfer'] += $total_transfer;
            $data['amortization_credit_purchases']['total'] += $total_cash_transfer;
        }
        //compra en efectivo
        else
        {
            $data['purchase_cash']['total_cash'] += $total_cash;
            $data['purchase_cash']['total_transfer'] += $total_transfer;
            $data['purchase_cash']['total'] += $total_cash_transfer;
        }
    }

    
    /**
     * 
     * Imprimir reporte a4
     *
     * @param  string $view
     * @param  string $temp_folder
     * @param  string $filename
     * @param  array $data
     */
    public function generalToPrintReport($view, $temp_folder, $filename, $pdf_data)
    {
        $view = view($view, $pdf_data);
        $html = $view->render();

        $pdf = new Mpdf(['mode' => 'utf-8']);
        $pdf->WriteHTML($html);

        return GeneralPdfHelper::getPreviewTempPdfWithFilename($temp_folder, $filename, $pdf->output('', 'S'));
    }

    
    /**
     * 
     * Generar excel
     *
     * @param  string $filename
     * @param  string $view
     * @param  array $data
     * @return GeneralFormatExport
     */
    public function generalExportReport($filename, $view, $data)
    {
        $general_format_export = new GeneralFormatExport();

        $general_format_export->view_name($view)
                                ->data($data);

        return $general_format_export->download($filename.'.xlsx');
    }


}
