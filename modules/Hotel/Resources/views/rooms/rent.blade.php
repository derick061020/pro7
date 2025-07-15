@extends('tenant.layouts.app')
@if(request()->get('view') == 'modal' && !request()->get('checkin'))
    @section('content')
    
            <tenant-hotel-rent 
                :room='@json($room)' 
                :affectation-igv-types='@json($affectation_igv_types)'
                :all-series='{{ $series }}'
                :is_modal='true'
                @if(isset($rent) && $rent)
                    :rent-data='@json($rent)'
                    :rent-payments='@json($rent->payments)'
                @endif
            ></tenant-hotel-rent>
    @endsection
    @push('styles')
    <style>
        .sidebar-left {
            display: none!important;
        }
        .header {
            display: none!important;
        }
        .page-header {
            display: none!important;
        }
        .footer {
            display: none!important;
        }
        .inner-wrapper {
            padding: 0!important;
        }
        .ws-flotante {
            display: none!important;
        }
        .page-content {
            padding: 0!important;
        }
        #main-wrapper {
            margin: 0!important;
        }
    </style>
    @endpush
@endif
@if(request()->get('view') == 'modal' && request()->get('checkin'))
    @section('content')
    
            <tenant-hotel-rent 
                :room='@json($room)' 
                :affectation-igv-types='@json($affectation_igv_types)'
                :all-series='{{ $series }}'
                :is_modal='true'
                @if(isset($rent) && $rent)
                    :rent-data='@json($rent)'
                    :rent-payments='@json($rent->payments)'
                @endif
            ></tenant-hotel-rent>
    @endsection
    
@endif
@if(request()->get('view') != 'modal')
    

    @section('content')
        <tenant-hotel-rent 
            :room='@json($room)' 
            :affectation-igv-types='@json($affectation_igv_types)'
            :all-series='{{ $series }}'
            :is_modal=false
        ></tenant-hotel-rent>
    @endsection
@endif
