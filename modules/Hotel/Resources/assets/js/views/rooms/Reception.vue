<template>
    <div>
        <div class="page-header pr-0">
            <h2>
                <a href="/hotels/reception">
                    <svg  xmlns="http://www.w3.org/2000/svg" style="margin-top: -5px;" width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-building-skyscraper"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l18 0" /><path d="M5 21v-14l8 -4v18" /><path d="M19 21v-10l-6 -4" /><path d="M9 9l0 .01" /><path d="M9 12l0 .01" /><path d="M9 15l0 .01" /><path d="M9 18l0 .01" /></svg>
                </a>
            </h2>
            <ol class="breadcrumbs">
                <li class="active"><span>VISTA GENERAL RECEPCIÓN</span></li>
            </ol>
            <div class="right-wrapper pull-right">
                <div class="btn-group flex-wrap">
                    <button
                        aria-expanded="false"
                        class="btn btn-custom btn-sm mt-2 mr-2 dropdown-toggle"
                        data-toggle="dropdown"
                        type="button"
                    >
                        <i class="fa fa-download"></i> Exportar
                        <span class="caret"></span>
                    </button>
                    <div
                        class="dropdown-menu"
                        role="menu"
                        style="
                            position: absolute;
                            will-change: transform;
                            top: 0px;
                            left: 0px;
                            transform: translate3d(0px, 42px, 0px);
                        "
                        x-placement="bottom-start"
                    >
                        <a
                            class="dropdown-item text-1"
                            href="#"
                            @click.prevent="clickExport()"
                        >Reporte recepción</a>

                    </div>
                </div>
            </div>
            
        </div>
        <div class="card tab-content-default row-new mb-0">
            <!-- <div class="card-header bg-info">
                <h3 class="my-0">Vista general recepción</h3>
            </div> -->
            <div class="card-body">
                <div class="row">
                    <!-- piso -->
                    <div class="col-md-3 col-sm-12 pb-2">
                        <el-select
                            v-model="hotel_floor_id"
                            :disabled="loading"
                            clearable
                            placeholder="Ubicación"
                            @change="searchRooms"
                        >
                            <el-option
                                v-for="f in floors"
                                :key="f.id"
                                :label="f.description"
                                :value="f.id"
                            >
                            </el-option>
                        </el-select>
                    </div>
                    <!-- Campo de busqueda -->
                    <div class="col-md-4 col-sm-12 pb-2">
                        <el-input
                            v-model="hotel_name_room"
                            clearable
                            placeholder="Buscar habitación"
                            prefix-icon="el-icon-search"
                            style="width: 100%;"
                            @input="searchRooms"
                        >
                        </el-input>
                    </div>
                    <!-- botones de status -->
                    <div class="col-md-5 col-sm-12 pb-2 text-right">
                        <el-button-group
                        >
                            <el-button
                                v-for="st in roomStatus"
                                :key="st"
                                :class="onGetColorStatus(st)"
                                :disabled="loading"
                                class="btn btn-sm"
                                size="mini"
                                @click="onFilterByStatus(st)"
                            >{{ st }}
                            </el-button
                            >
                        </el-button-group>
                    </div>
                </div>
                <div class="row card-columns" style="column-gap: 0px; -webkit-column-gap: 0px;">
                    <div v-for="ro in items"
                         :key="ro.id"
                         class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-xs-12 card hotel-rooms">
                        <el-card
                            :class="onGetColorStatus(ro.status)"
                            :style="{'background-color': ro.rent.is_current_booking ? 'rgb(53 117 229)' : GetColorStatus(ro.status, ro), 'height': '160px'}"
                            shadow="never"
                        >
                            <div class="">
                                <!-- <h4>{{ ro.status }}</h4> -->
                                  
                                   <span style="color:white!important;" class="text-muted">{{ ro.category.description }} - {{ ro.floor.description }}</span>
                                   <h3 class="mt-1 mb-0" style="display: flex; align-items: center; justify-content: space-between;">
                                    <div style="display: flex; align-items: center;">
                                        <svg style="color:white;" xmlns="http://www.w3.org/2000/svg"  width="32"  height="32"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-door"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 12v.01" /><path d="M3 21h18" /><path d="M6 21v-16a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v16" /></svg>
                                        <b style="color:white; margin-left: 5px;">{{ ro.name }}</b> 
                                        <el-tooltip v-if="ro.rent && ro.rent.observations" :content="ro.rent.observations" placement="top">
                                            <i class="el-icon-warning" style="color: rgb(251 201 201); margin-left: 5px; font-size: 30px;"></i>
                                        </el-tooltip>
                                        
                                            
                                    </div>
                                    <span v-if="ro.has_upcoming_booking" class="badge badge-warning" style="margin: 15px 15px 0 10px;font-size: 10px; padding: 3px 6px; margin-left: 5px;">
                                        <i class="el-icon-date"></i> Reserva próxima
                                    </span>
                                    </h3>
                                    <div v-if="ro.rent.output_date" style="color:white;position: absolute;top: 0.8rem;right: 0.8rem;background-color: rgba(255, 255, 255, 0.15);padding: 0.25rem 0.6rem;border-radius: 0.5rem;font-size: 0.75rem;display: flex;align-items: center;gap: 0.3rem;" class="timer">
                                        <p style="  word-spacing: 0.4em;margin: 0;color:white;">
                                            {{ getTimeRemaining(ro.rent.output_date, ro.rent.output_time).time }}
                                            <span style="  word-spacing: 0.15em;margin-top: -10px;color:white;font-size: 10px;display: flex;align-items: center;justify-content: center;">Dia Hora Min Seg</span>
                                        </p>
                                        
                                        <br>
                                        
                                        
                                    </div>
                                    <button 
                                    v-if="ro.rents && ro.rents.length > 0"
                                        @click="openRentHistoryDialog(ro)" 
                                        style="position: absolute;right: 15px;border-radius: 50%;height: 20px;width: 20px;background-color: rgba(255, 255, 255, 0.15);border: none;margin-top: -16px;font-size: 8px;padding: 0px;" 
                                        class="btn btn-primary"
                                        :title="'Ver historial de reservas (' + ro.rents.length + ')'"
                                    >
                                        {{ ro.rents.length }}
                                    </button>
                                    <span v-if="ro.debt > 0" style="
                                        position: absolute;
                                        right: 18px;
                                        margin-top: 18px;
                                        background: #fff;
                                        padding: 1px 6px;
                                        font-size: 8px;
                                        border-radius: 10px;
                                    "><b>DEBE: {{ ro.debt }} SOLES</b>
                                    </span>
                                   <p style="color:white;width: 50%;overflow: hidden;white-space: nowrap;" v-if="ro.description && ro.status === 'DISPONIBLE' && !ro.is_clean" class="description">{{ ro.description }}</p>
                                   <br v-if="!ro.description && ro.status === 'DISPONIBLE' && !ro.is_clean">

                                <template v-if="ro.status === 'LIMPIEZA' || ro.is_clean" >
                                    <br v-if="!ro.rent.customer">
                                    <br>
                                    <div style=" width: 100%; display: flex; justify-content: space-between; align-items: center;">
                                        <div style=" width: 100%;">
                                            <p v-if="ro.rent.customer" style="color:white;width: 50%;overflow: hidden;white-space: nowrap;" class="m-0"><b>{{ ro.rent.customer.name  }} </b>
                                            </p>
                                        </div>
                                    </div>
                                   
                                    <el-button
                                        :disabled="loading"
                                        :loading="loading"
                                        title="Finalizar limpieza"
                                        class="btn btn-block btn-info"
                                        @click="onFinalizeClean(ro)"
                                        v-if="ro.is_clean"
                                    >
                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-spray"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 10m0 2a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v7a2 2 0 0 1 -2 2h-4a2 2 0 0 1 -2 -2z" /><path d="M6 10v-4a1 1 0 0 1 1 -1h2a1 1 0 0 1 1 1v4" /><path d="M15 7h.01" /><path d="M18 9h.01" /><path d="M18 5h.01" /><path d="M21 3h.01" /><path d="M21 7h.01" /><path d="M21 11h.01" /><path d="M10 7h1" /></svg>
                                        Finalizar limpieza
                                    </el-button>
                                    <el-button 
                                        v-if="!ro.is_clean"
                                        :disabled="loading"
                                        :loading="loading"
                                        title="Limpieza Rápida"
                                        class="btn btn-block btn-info"
                                        @click="roomActions.room = ro;
                                        openQuickCleanModal()">
                                        <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-spray"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 10m0 2a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v7a2 2 0 0 1 -2 2h-4a2 2 0 0 1 -2 -2z" /><path d="M6 10v-4a1 1 0 0 1 1 -1h2a1 1 0 0 1 1 1v4" /><path d="M15 7h.01" /><path d="M18 9h.01" /><path d="M18 5h.01" /><path d="M21 3h.01" /><path d="M21 7h.01" /><path d="M21 11h.01" /><path d="M10 7h1" /></svg>
                                        Limpieza rápida
                                    </el-button>

                                </template>
                                <template v-if="ro.status === 'MANTENIMIENTO' && !ro.is_clean">
                                    <h4 class="text-warning text-center mb-0">
                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="20"  height="20"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-tool"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 10h3v-3l-3.5 -3.5a6 6 0 0 1 8 8l6 6a2 2 0 0 1 -3 3l-6 -6a6 6 0 0 1 -8 -8l3.5 3.5" /></svg>
                                        <b style="color:white;margin-left:-30px;">En mantenimiento:</b>
                                    </h4>
                                    <p class="text-center">Debe cambiar el estado a <b>Disponible</b> en el módulo Habitaciones.</p>
                                </template>
                                
                                <template v-if="ro.status === 'OCUPADO' && !ro.rent.is_current_booking && !ro.is_clean">
                                    <div style=" width: 100%; display: flex; justify-content: space-between; align-items: center;">
                                        <div style=" width: 100%;">
                                            <p style="color:white;width: 50%;overflow: hidden;white-space: nowrap;" class="m-0"><b>{{ ro.rent.customer.name }} </b>
                                            </p>
                                            <span v-if="ro.rent.is_current_booking" class="badge badge-white" style="margin: 5px -1px;background: white;font-size: 10px; padding: 3px 6px; margin-left: 5px;">
                                                <i class="el-icon-date"></i> Reserva activa
                                            </span>
                                            <br v-if="ro.rent && !ro.rent.matricula">
                                            <span v-if="ro.rent && ro.rent.matricula" style="color:white;"><b>Matricula: {{ ro.rent.matricula }}</b></span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3">
                                            <el-button
                                                title="Agregar productos"
                                                class="btn btn-block btn-danger"
                                                data-toggle="tooltip"
                                                @click="onGoToAddProducts(ro)"
                                            >
                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-hotel-service"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8.5 10a1.5 1.5 0 0 1 -1.5 -1.5a5.5 5.5 0 0 1 11 0v10.5a2 2 0 0 1 -2 2h-7a2 2 0 0 1 -2 -2v-2c0 -1.38 .71 -2.444 1.88 -3.175l4.424 -2.765c1.055 -.66 1.696 -1.316 1.696 -2.56a2.5 2.5 0 1 0 -5 0a1.5 1.5 0 0 1 -1.5 1.5z" /></svg> 
                                            </el-button>
                                        </div>
                                        <div class="col-9">
                                            <el-button 
                                                class="btn btn-block btn-danger" 
                                                style="background-color: white!important;color: black;"
                                                @click="openRoomActionsModal(ro)">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-door-exit">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                    <path d="M13 12v.01" />
                                                    <path d="M3 21h18" />
                                                    <path d="M5 21v-16a2 2 0 0 1 2 -2h6m4 10.5v7.5" />
                                                    <path d="M21 7h-7m3 -3l-3 3l3 3" />
                                                </svg> Ocupado
                                            </el-button>
                                        </div>
                                    </div>
                                </template>
                                <template v-if="ro.status === 'RESIDENCIA' && !ro.is_clean">
                                    <div style=" width: 100%;display: flex; justify-content: space-between; align-items: center;">
                                        <div style=" width: 100%;">
                                            <p style="color:white;width: 50%;overflow: hidden;white-space: nowrap;" class="m-0"><b>{{ ro.rent.customer.name }} </b>
                                            </p>
                                            <br v-if="ro.rent && !ro.rent.matricula">
                                            <span v-if="ro.rent && ro.rent.matricula" style="color:white;"><b>Matricula: {{ ro.rent.matricula }}</b></span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3">
                                            <el-button
                                                title="Agregar productos"
                                                class="btn btn-block btn-danger"
                                                data-toggle="tooltip"
                                                @click="onGoToAddProducts(ro)"
                                            >
                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-hotel-service"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8.5 10a1.5 1.5 0 0 1 -1.5 -1.5a5.5 5.5 0 0 1 11 0v10.5a2 2 0 0 1 -2 2h-7a2 2 0 0 1 -2 -2v-2c0 -1.38 .71 -2.444 1.88 -3.175l4.424 -2.765c1.055 -.66 1.696 -1.316 1.696 -2.56a2.5 2.5 0 1 0 -5 0a1.5 1.5 0 0 1 -1.5 1.5z" /></svg> 
                                            </el-button>
                                        </div>
                                        <div class="col-9">
                                            <el-button 
                                                class="btn btn-block btn-danger" 
                                                style="background-color: white!important;color: black;"
                                                @click="openRoomActionsModal(ro)">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-door-exit">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                    <path d="M13 12v.01" />
                                                    <path d="M3 21h18" />
                                                    <path d="M5 21v-16a2 2 0 0 1 2 -2h6m4 10.5v7.5" />
                                                    <path d="M21 7h-7m3 -3l-3 3l3 3" />
                                                </svg> Ocupado
                                            </el-button>
                                        </div>
                                    </div>
                                </template>
                                <template v-if="ro.status === 'OCUPADO' && ro.rent.is_current_booking && !ro.is_clean">
                                    <div style=" width: 100%;display: flex; justify-content: space-between; align-items: center;">
                                        <div style=" width: 100%;">
                                            <p style="color:white;width: 50%;overflow: hidden;white-space: nowrap;" class="m-0"><b>{{ ro.rent.customer.name }}</b>
                                                <el-tooltip content="Ver/Editar Observaciones" placement="top">
                                                    <el-button
                                                        type="text"
                                                        size="mini"
                                                        @click="openObservationsModal(ro)"
                                                        style="color: white; padding: 0 5px;"
                                                    >
                                                        <i class="el-icon-edit-outline" style="font-size: 16px;"></i>
                                                    </el-button>
                                                </el-tooltip>
                                            </p>
                                            <br v-if="ro.rent && !ro.rent.matricula">
                                            <span v-if="ro.rent && ro.rent.matricula" style="color:white;"><b>Matricula: {{ ro.rent.matricula }}</b></span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3">
                                            <el-button
                                                title="Agregar productos"
                                                class="btn btn-block btn-danger"
                                                data-toggle="tooltip"
                                                @click="onGoToAddProducts(ro)"
                                            >
                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-hotel-service"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8.5 10a1.5 1.5 0 0 1 -1.5 -1.5a5.5 5.5 0 0 1 11 0v10.5a2 2 0 0 1 -2 2h-7a2 2 0 0 1 -2 -2v-2c0 -1.38 .71 -2.444 1.88 -3.175l4.424 -2.765c1.055 -.66 1.696 -1.316 1.696 -2.56a2.5 2.5 0 1 0 -5 0a1.5 1.5 0 0 1 -1.5 1.5z" /></svg> 
                                            </el-button>
                                        </div>
                                        <div class="col-9">
                                            <el-button
                                                v-if="ro.rent && ro.rent.status === 'RESERVADO'"
                                                title="Ir al check-in"
                                                style="background-color: white!important;border-color: #67C23A; color: black;"
                                                class="btn btn-block btn-success"
                                                @click="onGoToCheckin(ro)"
                                                :loading="loading && currentCheckingInId === ro.rent.id"
                                            >Reservado
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-door-enter">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                    <path d="M13 12v.01" />
                                                    <path d="M3 21h18" />
                                                    <path d="M5 21v-16a2 2 0 0 1 2 -2h7.5m2.5 10.5v7.5" />
                                                    <path d="M21 7h-7m3 -3l-3 3l3 3" />
                                                </svg>
                                            </el-button>
                                            <el-button
                                                v-else-if="isCheckoutTimeReached(ro.rent)"
                                                title="Ir al checkout"
                                                style="background-color: white!important;border-color: rgb(53 117 229); color: black;"
                                                class="btn btn-block btn-danger"
                                                @click="onGoToCheckout(ro)"
                                            >Check-out 
                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-door-exit"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M13 12v.01" /><path d="M3 21h18" /><path d="M5 21v-16a2 2 0 0 1 2 -2h7.5m2.5 10.5v7.5" /><path d="M14 7h7m-3 -3l3 3l-3 3" /></svg>
                                            </el-button>
                                        </div>
                                    </div>
                                </template>
                                <br v-if="!ro.description && ro.status === 'DISPONIBLE' && !ro.is_clean">
                                <el-button
                                    v-if="ro.status === 'DISPONIBLE' && !ro.is_clean"
                                    class="btn btn-block"
                                    style="background-color: white;color: black;"
                                    @click="onToRent(ro)"
                                >
                                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-door-enter">
                                        <g transform="scale(-1 1) translate(-24 0)">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                            <path d="M13 12v.01" />
                                            <path d="M3 21h18" />
                                            <path d="M5 21v-16a2 2 0 0 1 2 -2h6m4 10.5v7.5" />
                                            <path d="M21 7h-7m3 -3l-3 3l3 3" />
                                        </g>
                                    </svg> Disponible
                                </el-button>
                            </div>
                           
                        </el-card>
                    </div>
                </div>
            </div>
        </div>
        <!-- Dialog de Observaciones -->
        <el-dialog
            title="Observaciones"
            :visible.sync="showObservationsModal"
            width="50%"
            :close-on-click-modal="false"
            :show-close="!savingObservations"
            :close-on-press-escape="!savingObservations"
            custom-class="observations-dialog"
            @close="closeObservationsModal"
        >
            <div>
                <el-input
                    type="textarea"
                    :rows="5"
                    placeholder="Ingrese observaciones"
                    v-model="currentRent.observations"
                    :disabled="savingObservations"
                    class="large-textarea"
                >
                </el-input>
            </div>
            <span slot="footer" class="dialog-footer">
                <el-button @click="closeObservationsModal" :disabled="savingObservations">Cancelar</el-button>
                <el-button 
                    type="primary" 
                    @click="saveObservations" 
                    :loading="savingObservations"
                    :disabled="savingObservations"
                >
                    Guardar
                </el-button>
            </span>
        </el-dialog>
        <ModalRoomRates
            :room="room"
            :visible.sync="openModalRoomRates"
            @onAddRoomRate="onAddRoomRate"
            @onDeleteRate="onDeleteRate"
        ></ModalRoomRates>
        <ExtendTimeRoom
            :room="roomToExtend"
            :visible.sync="openDialogExtendTimeRoom"
            @onRefresh="onRefresh">
        </ExtendTimeRoom>
        <reception-export 
            :show-dialog.sync="showExportDialog"
            :user-type="userType"
            :establishment-id="establishmentId"
        >
        </reception-export>
        
        <!-- Modal de Acciones de Habitación -->
        <!-- Diálogo para cambiar de habitación -->
        <el-dialog
            title="Cambiar de habitación"
            :visible.sync="showChangeRoomModal"
            width="400px"
            custom-class="change-room-dialog"
            :close-on-click-modal="false">
            <div v-loading="loading">
                <div class="mb-3" style="font-size: 15px; color: #606266;">
                    <p>Seleccione la nueva habitación para el huésped:</p>
                </div>
                <div class="d-flex align-items-center mb-2" v-if="lastNonHiddenItemPrice > 0">
                    <i class="el-icon-money mr-2"></i>
                    <span class="font-weight-bold">Precio unitario actual:</span>
                    <span class="ml-2 text-success">
                      S/ {{ lastNonHiddenItemPrice}}
                    </span>
                  </div>
                
                <el-select 
                    v-model="newRoomId" 
                    placeholder="Seleccione una habitación"
                    style="width: 100%; margin-bottom: 15px;"
                    filterable
                    clearable
                    @change="onRoomChange">
                    <el-option
                        v-for="room in availableRooms"
                        :key="room.id"
                        :label="`${room.name} - ${room.category.description}`"
                        :value="room.id">
                    </el-option>
                </el-select>

                <div v-if="newRoomId && newRoomRates.length > 0" class="mb-3">
                    <div class="mb-2" style="font-size: 14px; color: #606266;">
                        <span>Seleccione la tarifa:</span>
                    </div>
                    <el-select 
                        v-model="selectedRate" 
                        placeholder="Seleccione una tarifa"
                        style="width: 100%; margin-bottom: 15px;"
                        filterable>
                        <el-option
                            v-for="rate in newRoomRates"
                            :key="rate.id"
                            :label="`${rate.rate.description} - S/ ${rate.price}`"
                            :value="rate.id">
                        </el-option>
                    </el-select>
                </div>
                <div v-else-if="loadingRates" class="text-center" style="margin: 10px 0;">
                    <i class="el-icon-loading"></i> Cargando tarifas...
                </div>

                <div class="mb-3" style="font-size: 14px; color: #909399;">
                    <p>Observaciones (opcional):</p>
                </div>
                <el-input
                    type="textarea"
                    :rows="2"
                    v-model="changeRoomObservations"
                    placeholder="Motivo del cambio de habitación"
                    style="margin-bottom: 15px;">
                </el-input>
            </div>
            
            <span slot="footer" class="dialog-footer">
                <el-button @click="showChangeRoomModal = false">Cancelar</el-button>
                <el-button 
                    type="primary" 
                    :loading="changingRoom"
                    :disabled="(!newRoomId || (newRoomRates.length > 0 && !selectedRate))"
                    @click="confirmRoomChange">
                    Cambiar Habitación
                </el-button>
            </span>
        </el-dialog>

        <el-dialog
            :visible.sync="roomActions.visible"
            :title="'Acciones - Habitación ' + (roomActions.room ? roomActions.room.name : '')"
            width="500px"
            custom-class="room-actions-dialog"
            :close-on-click-modal="false"
            :close-on-press-escape="false"
            append-to-body>
            
            <!-- Tarjeta de Observaciones -->
            <div v-if="roomActions.room && roomActions.room.rent && roomActions.room.rent.observations" class="observations-card">
                <div class="observations-header">
                    <i class="el-icon-document"></i>
                    <span>Observaciones</span>
                </div>
                <div class="observations-content">
                    {{ roomActions.room.rent.observations }}
                </div>
            </div>
            
            <div class="row room-actions-container">
                <el-button 
                    type="primary" 
                    class="action-button col col-lg-5 col-sm-12"
                    @click="onGoToCheckout(roomActions.room); roomActions.visible = false">
                    <i class="el-icon-sold-out"></i>
                    <span>Check-out</span>
                </el-button>
                
                <el-button 
                    type="primary" 
                    class="action-button col col-lg-6 col-sm-12"
                    @click="openQuickCleanModal">
                    <i class="el-icon-loading"></i>
                    Limpieza Rápida
                </el-button>
                <el-button 
                    type="success" 
                    class="action-button col col-lg-5 col-sm-12"
                    @click="ShowDialogExtendTimeRoom(roomActions.room); roomActions.visible = false">
                    <i class="el-icon-time"></i>
                    <span>Extender estadía</span>
                </el-button>
                
                <el-button 
                    type="warning" 
                    class="action-button col col-lg-6 col-sm-12"
                    @click="openObservationsModal(roomActions.room); roomActions.visible = false">
                    <i class="el-icon-edit-outline"></i>
                    <span>Ver/Editar observaciones</span>
                </el-button>
                
                <el-button 
                    type="info" 
                    class="action-button col col-lg-5 col-sm-12"
                    @click="showChangeRoomDialog(roomActions.room); roomActions.visible = false">
                    <i class="el-icon-refresh"></i>
                    <span>Cambiar habitación</span>
                </el-button>
                
                <el-button 
                    type="danger" 
                    class="action-button col col-lg-6 col-sm-12"
                    @click="handleDelete(roomActions.room)">
                    <i class="el-icon-delete"></i>
                    <span>Eliminar registro</span>
                </el-button>
            </div>
            
            <span slot="footer" class="dialog-footer">
                <el-button @click="roomActions.visible = false">Cerrar</el-button>
            </span>
        </el-dialog>

        <!-- Modal de Limpieza Rápida -->
        <el-dialog
            :visible.sync="showQuickCleanModal"
            title="Limpieza Rápida"
            width="400px"
            custom-class="quick-clean-modal"
            :close-on-click-modal="false"
            :close-on-press-escape="false"
            append-to-body>
            
            <div class="quick-clean-content">
                <div class="quick-clean-header">
                    <p>Seleccione el limpiador para esta habitación:</p>
                </div>
                
                <div class="cleaner-select-container">
                    <el-select
                        v-model="selectedCleaner"
                        placeholder="Seleccionar limpiador"
                        style="width: 100%">
                        <el-option
                            v-for="cleaner in cleaners"
                            :key="cleaner.id"
                            :label="cleaner.name"
                            :value="cleaner">
                            <span>{{ cleaner.name }}</span>
                            <small style="color: #909399; margin-left: 10px">ID: {{ cleaner.id }}</small>
                        </el-option>
                    </el-select>
                </div>
                
                <div class="cleaner-actions">
                    <el-button 
                        type="success"
                        :loading="assigningCleaner"
                        :disabled="!selectedCleaner"
                        @click="assignCleaner">
                        Iniciar Limpieza
                    </el-button>
                    <el-button 
                        type="danger"
                        @click="showQuickCleanModal = false">
                        Cancelar
                    </el-button>
                </div>
            </div>
        </el-dialog>

        <!-- Rent History Dialog -->
        <el-dialog
            title="Historial de Reservas"
            :visible.sync="showRentHistoryDialog"
            width="70%"
            :before-close="() => showRentHistoryDialog = false"
        >
            <el-table
                :data="currentRoomRents"
                border
                style="width: 100%"
                v-loading="loadingRentHistory"
            >
                <el-table-column
                    prop="id"
                    label="#"
                    width="80"
                >
                    <template slot-scope="scope">
                        {{ scope.$index + 1 }}
                    </template>
                </el-table-column>
                <el-table-column
                    prop="customer.name"
                    label="Cliente"
                    min-width="200"
                >
                    <template slot-scope="scope">
                        {{ scope.row.customer ? scope.row.customer.name : 'N/A' }}
                    </template>
                </el-table-column>
                <el-table-column
                    label="Check-in"
                    width="180"
                >
                    <template slot-scope="scope">
                        {{ scope.row.input_date }} {{ scope.row.input_time || '' }}
                    </template>
                </el-table-column>
                <el-table-column
                    label="Check-out"
                    width="180"
                >
                    <template slot-scope="scope">
                        {{ scope.row.output_date }} {{ scope.row.output_time || '' }}
                    </template>
                </el-table-column>
                <el-table-column
                    prop="status"
                    label="Estado"
                    width="120"
                >
                    <template slot-scope="scope">
                        <el-tag :type="getStatusTagType(scope.row.status)" size="small">
                            {{ scope.row.status }}
                        </el-tag>
                    </template>
                </el-table-column>
                <el-table-column
                    label="Tipo"
                    width="120"
                >
                    <template slot-scope="scope">
                        {{ scope.row.is_booking ? 'Reserva' : 'Check-in' }}
                    </template>
                </el-table-column>
                <el-table-column
                    prop="observations"
                    label="Observaciones"
                    min-width="200"
                    show-overflow-tooltip
                ></el-table-column>
            </el-table>

            <span slot="footer" class="dialog-footer">
                <el-button @click="showRentHistoryDialog = false">Cerrar</el-button>
            </span>
        </el-dialog>
    </div>
</template>

<style scoped>
/* Estilos para el textarea grande */
.large-textarea textarea {
    min-height: 200px !important;
    resize: vertical;
}

/* Estilos para el diálogo de cambio de habitación */
.change-room-dialog .el-dialog__header {
    padding: 15px 20px 10px;
    border-bottom: 1px solid #e8e8e8;
    margin: 0;
}

.change-room-dialog .el-dialog__header .el-dialog__title {
    font-size: 16px;
    font-weight: 500;
    color: #303133;
}

.change-room-dialog .el-dialog__body {
    padding: 20px;
    padding-bottom: 10px;
}
textarea.el-textarea__inner {
    height: 200px !important;
}
.change-room-dialog .el-dialog__footer {
    padding: 10px 20px 20px;
    text-align: right;
    border-top: 1px solid #e8e8e8;
}

.change-room-dialog .el-button {
    padding: 9px 15px;
    border-radius: 4px;
    font-size: 14px;
}

.change-room-dialog .el-button--primary {
    background-color: #409EFF;
    border-color: #409EFF;
}

.change-room-dialog .el-button--primary:disabled {
    background-color: #a0cfff;
    border-color: #a0cfff;
    color: #fff;
}

.change-room-dialog .el-input__inner {
    height: 36px;
    line-height: 36px;
}

.change-room-dialog .el-textarea__inner {
    min-height: 60px !important;
    font-size: 14px;
}

/* Estilos para la lista de habitaciones */
.room-option {
    display: flex;
    align-items: center;
    padding: 8px 15px;
}

.room-option__info {
    margin-left: 10px;
}

.room-option__name {
    font-weight: 500;
    margin-bottom: 2px;
}

.room-option__details {
    font-size: 12px;
    color: #909399;
}

/* Estilos para la tarjeta de observaciones */
.observations-card {
    background-color: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #409EFF;
    margin-bottom: 20px;
    overflow: hidden;
    box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);
}

.observations-header {
    background-color: #f1f7ff;
    padding: 10px 15px;
    display: flex;
    align-items: center;
    font-weight: 500;
    color: #303133;
}

.observations-header i {
    margin-right: 8px;
    font-size: 16px;
    color: #409EFF;
}

.observations-content {
    padding: 15px;
    color: #606266;
    line-height: 1.5;
    white-space: pre-line;
}

/* Estilos para el modal de limpieza rápida */
.quick-clean-modal .el-dialog__header {
    background-color: #e6f3ff;
    color: #1989fa;
    font-weight: 600;
}

.quick-clean-content {
    padding: 20px;
}

.quick-clean-header {
    margin-bottom: 20px;
    color: #606266;
    font-size: 14px;
}

.cleaner-select-container {
    margin-bottom: 25px;
}

.cleaner-select-container .el-select {
    width: 100%;
}

.cleaner-select-container .el-select .el-input__inner {
    height: 40px;
    line-height: 40px;
}

.cleaner-select-container .el-select-dropdown__item {
    padding: 0 20px;
    height: 40px;
    line-height: 40px;
    font-size: 14px;
}

.cleaner-select-container .el-select-dropdown__item span {
    display: block;
    font-weight: 500;
}

.cleaner-select-container .el-select-dropdown__item small {
    display: block;
    font-size: 12px;
    color: #909399;
    margin-top: 4px;
}

.room-actions-dialog .el-dialog__body {
    padding: 20px;
}
.room-actions-container {
    display: flex;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-bottom: 20px;
}
.action-button {
    display: flex;
    flex-direction: column;
    height: 100%;
    padding: 15px 10px;
    text-align: center;
    border-radius: 8px;
    transition: all 0.3s;
}
.action-button i {
    font-size: 24px;
    margin-bottom: 8px;
}
.action-button span {
    font-size: 13px;
    line-height: 1.2;
}
.action-button:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}
</style>

<script>
import ExtendTimeRoom from './partials/ExtendTimeRoom.vue';
import ModalRoomRates from "./RoomRates";
import ReceptionExport from './partials/ReceptionExport.vue';

export default {
    name: 'Reception',
    components: {
        ExtendTimeRoom,
        ModalRoomRates,
        ReceptionExport
    },
    props: {
        roomStatus: {
            type: Array,
            required: true
        },
        floors: {
            type: Array,
            required: true
        },
        rooms: {
            type: Array,
            required: true,
            default: () => []
        },
        userType: {
            type: String,
            required: true
        }
    },
    data() {
        return {
            showRentHistoryDialog: false,
            currentRoomRents: [],
            currentRoom: null,
            loadingRentHistory: false,
            currentTime: new Date(),
            timer: null,
            loading: false,
            roomActions: {
                visible: false,
                room: null
            },
            lastNonHiddenItemPrice: 0,
            // Para cambio de habitación
            showChangeRoomModal: false,
            availableRooms: [],
            newRoomId: null,
            newRoomRates: [],
            selectedRate: null,
            selectedCleaner: null,
            assigningCleaner: false,
            loadingRates: false,
            changingRoom: false,
            changeRoomObservations: '',
            currentRentId: null,
            roomRates: [],
            roomRate: {},
            showObservationsModal: false,
            currentRoom: null,
            observations: '',
            savingObservations: false,
            currentRent: {
                id: null,
                observations: ''
            },
            openModalRoomRates: false,
            showExportDialog: false,
            currentCheckingInId: null,
            items: [],
            hotel_floor_id: null,
            hotel_name_room: null,
            room_status: '',
            showQuickCleanModal: false,
            cleaners: [],
            loadingCleaners: false
        };
    },
    mounted() {
        this.searchRooms();
        // Actualizar el contador cada segundo
        this.timer = setInterval(() => {
            this.currentTime = new Date();
            // Forzar actualización del componente
            this.$forceUpdate();
        }, 1000);
    },
    beforeDestroy() {
        // Limpiar el intervalo cuando el componente se destruye
        if (this.timer) clearInterval(this.timer);
    },
    methods: {
        getStatusTagType(status) {
            const statusMap = {
                'PENDIENTE': 'warning',
                'CONFIRMADO': 'success',
                'EN CURSO': 'primary',
                'FINALIZADO': 'info',
                'CANCELADO': 'danger',
                'ELIMINADO': 'danger',
                'NO SHOW': 'danger'
            };
            return statusMap[status] || 'info';
        },
        
        openRentHistoryDialog(room) {
            this.currentRoom = room;
            this.currentRoomRents = room.rents || [];
            this.showRentHistoryDialog = true;
            
            // If you need to load additional data:
            // this.loadRentHistory(room.id);
        },
        
        async loadRentHistory(roomId) {
            this.loadingRentHistory = true;
            try {
                const response = await this.$http.get(`/hotels/rooms/${roomId}/rents`);
                this.currentRoomRents = response.data.data || [];
            } catch (error) {
                console.error('Error loading rent history:', error);
                this.$message.error('Error al cargar el historial de rentas');
            } finally {
                this.loadingRentHistory = false;
            }
        },
        async showRoomActions(room) {
            this.loadingCleaners = true;
            try {
                this.roomActions.room = room;
                this.roomActions.visible = true;
                await this.loadCleaners();
            } catch (error) {
                console.error('Error loading cleaners:', error);
                this.$message({
                    message: 'Error al cargar los limpiadores',
                    type: 'error'
                });
            } finally {
                this.loadingCleaners = false;
            }
        },
        async loadCleaners() {
            try {
                this.loadingCleaners = true;
                const response = await this.$http.get('/hotels/users/type/cleaner');
                this.cleaners = response.data.users;
            } catch (error) {
                console.error('Error loading cleaners:', error);
                this.$message({
                    message: 'Error al cargar los limpiadores',
                    type: 'error'
                });
            } finally {
                this.loadingCleaners = false;
            }
        },
        
        async openQuickCleanModal() {
            try {
                await this.loadCleaners();
                if (this.cleaners.length > 0) {
                    this.showQuickCleanModal = true;
                } else {
                    this.$message({
                        message: 'No hay limpiadores disponibles',
                        type: 'warning'
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                this.$message({
                    message: 'Error al cargar los limpiadores',
                    type: 'error'
                });
            }
        },
        openRoomActionsModal(room) {
            this.roomActions.room = room;
            this.roomActions.visible = true;
        },
        handleDelete(room) {
            this.$confirm('¿Está seguro de eliminar este registro?', 'Confirmación', {
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                type: 'warning'
            }).then(() => {
                this.handleRoomCommand({ action: 'delete', room });
                this.roomActions.visible = false;
            }).catch(() => {
                // User cancelled
            });
        },
        handleRoomCommand(command) {
            this.selectedRoom = command.room;
            switch(command.action) {
                case 'checkout':
                    this.onGoToCheckout(command.room);
                    break;
                case 'extend':
                    this.ShowDialogExtendTimeRoom(command.room);
                    break;
                case 'observations':
                    this.openObservationsModal(command.room);
                    break;
                case 'add_products':
                    this.onGoToAddProducts(command.room);
                    break;
                case 'delete':
                    this.handleDeleteRent(command.room);
                    break;
                case 'change_room':
                    this.showChangeRoomDialog(command.room);
                    break;
            }
        },
        
        async showChangeRoomDialog(room) {
            try {
                this.loading = true;
                this.currentRentId = room.rent.id;
                
                // Obtener habitaciones disponibles
                const response = await this.$http.get(`/hotels/reception/rooms/${room.id}/available-for-change`);
                this.availableRooms = response.data.rooms;

                if (!room.rent.history) this.lastNonHiddenItemPrice = 0;
                const history = JSON.parse(room.rent.history);
                // Find the last non-hidden item in reverse order
                const lastItem = [...history].reverse().find(item => !item.hidden);
                this.lastNonHiddenItemPrice = lastItem.unit_price || 0;
                // Resetear valores
                this.newRoomId = null;
                this.newRoomRates = [];
                this.selectedRate = null;
                
                this.showChangeRoomModal = true;
                
            } catch (error) {
                console.error('Error al cargar habitaciones disponibles:', error);
                this.$message.error('Error al cargar las habitaciones disponibles');
            } finally {
                this.loading = false;
            }
        },
        
        async confirmRoomChange() {
            if (!this.newRoomId) {
                this.$message.warning('Por favor seleccione una habitación');
                return;
            }
            
            if (this.newRoomRates.length > 0 && !this.selectedRate) {
                this.$message.warning('Por favor seleccione una tarifa');
                return;
            }
            
            try {
                this.changingRoom = true;
                
                const response = await this.$http.post(`/hotels/reception/rents/${this.currentRentId}/change-room`, {
                    new_room_id: this.newRoomId,
                    hotel_rate_id: this.selectedRate,
                    observations: this.changeRoomObservations
                });
                
                this.$message.success('Habitación cambiada exitosamente');
                this.showChangeRoomModal = false;
                this.searchRooms(); // Actualizar la lista de habitaciones
                
                // Resetear el formulario
                this.newRoomId = null;
                this.newRoomRates = [];
                this.selectedRate = null;
                this.changeRoomObservations = '';
                
            } catch (error) {
                console.error('Error al cambiar de habitación:', error);
                const errorMessage = (error.response && error.response.data && error.response.data.message) 
                    ? error.response.data.message 
                    : 'Error al cambiar de habitación';
                this.$message.error(errorMessage);
            } finally {
                this.changingRoom = false;
            }
        },
        async onRoomChange(roomId) {
            if (!roomId) {
                this.newRoomRates = [];
                this.selectedRate = null;
                return;
            }
            
            try {
                this.loadingRates = true;
                const response = await this.$http.get(`/hotels/rooms/${roomId}/rates`);
                this.newRoomRates = response.data.room_rates || [];
                
                // Si solo hay una tarifa, seleccionarla automáticamente
                if (this.newRoomRates.length === 1) {
                    this.selectedRate = this.newRoomRates[0].id;
                } else if (this.newRoomRates.length > 1) {
                    this.selectedRate = null; // Dejar que el usuario seleccione
                }
            } catch (error) {
                console.error('Error al cargar tarifas de la habitación:', error);
                this.$message.error('Error al cargar las tarifas de la habitación');
                this.newRoomRates = [];
            } finally {
                this.loadingRates = false;
            }
        },
        
        isCheckoutTimeReached(rent) {
            if (!rent || !rent.output_date || !rent.output_time) return false;
            
            const now = new Date();
            const [year, month, day] = rent.output_date.split('-').map(Number);
            const [hours, minutes] = rent.output_time.split(':').map(Number);
            
            const checkoutDate = new Date(year, month - 1, day, hours, minutes);
            
            return now >= checkoutDate;
        },
        onFinalizeClean(room) {
            const text = `Está a punto de terminar la limpieza de la habitación ${room.name}`;
            this.$confirm(text, "Atención", {
                confirmButtonText: "Si",
                cancelButtonText: "No",
                type: "warning",
            })
                .then(() => {
                    this.loading = true;
                    const payload = {
                        status: "DISPONIBLE",
                    };
                    this.$http
                        .post(`/hotels/rooms/${room.id}/change-status`, payload)
                        .then((response) => {
                            if(!response.data.status) {
                                room.status = "DISPONIBLE";
                            }
                            room.is_clean = false;
                            room.cleaner_id = null;
                            this.items = this.items.map((r) => {
                                if (r.id === room.id) {
                                    return room;
                                }
                                return r;
                            });
                            this.$message({
                                type: "success",
                                message: response.data.message,
                            });
                            window.location.reload();
                        })
                        .finally(() => (this.loading = false));
                })
                .catch();
        },
        onGoToCheckin(room) {
            // Redirect to rent page in edit mode with checkin flag
            this.currentCheckingInId = room.rent.id;
            window.location.href = `/hotels/reception/${room.rent.id}/rent?mode=edit&view=modal&checkin=true`;
        },
        onGoToCheckout(room) {
            window.location.href = `/hotels/reception/${room.rent.id}/rent/checkout`;
        },
        onGoToAddProducts(room) {
            window.location.href = `/hotels/reception/${room.rent.id}/rent/products`;
        },
        onDeleteRate(rateId) {
            this.room.rates = this.room.rates.filter((r) => r.id !== rateId);
        },
        onAddRoomRate(rate) {
            this.room.rates.push(rate);
        },
        onToRent(room) {
            if (room.rates.length > 0) {
                window.location.href = `/hotels/reception/${room.id}/rent`;
            } else {
                this.room = room;
                this.openModalRoomRates = true;
            }
       },
        async assignCleaner() {
            if (!this.selectedCleaner) {
                this.$message({
                    message: 'Por favor, seleccione un limpiador',
                    type: 'warning'
                });
                return;
            }

            this.assigningCleaner = true;
            try {
                await this.$http.post(`/hotels/rooms/${this.roomActions.room.id}/clean`, {
                    cleaner_id: this.selectedCleaner.id
                });
                
                this.$message({
                    message: 'Limpieza asignada correctamente',
                    type: 'success'
                });
                
                // Actualizar la habitación
                
                this.showQuickCleanModal = false;
                this.selectedCleaner = null;
                this.roomActions.visible = false;
                
                window.location.reload();
                await this.loadRooms();
            } finally {
                this.assigningCleaner = false;
            }
        },

        searchRooms() {
            this.loading = true;
            let form = {
                hotel_status_room: this.hotel_status_room,
                hotel_name_room: this.hotel_name_room,
                hotel_floor_id: this.hotel_floor_id,

            }
            this.$http
                .post("/hotels/reception/search", form)
                .then((response) => {
                    // console.error(response.data)
                    this.items = response.data.rooms;
                    console.log(this.items)

                })
                .finally(() => {
                    this.loading = false;
                })
        },
        onFilterByStatus(status = "") {
            // Si se presiona dos veces la misma opcion, se cancelaria
            if(this.hotel_status_room == status){
                this.hotel_status_room = null
            }else {
                this.hotel_status_room = status
            }
            this.searchRooms()
            return null;
            this.loading = true;
            const params = {
                status,
                hotel_floor_id: this.hotel_floor_id,
            };
            this.$http
                .get("/hotels/reception", {params})
                .then((response) => {
                    this.items = response.data.rooms;
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        onGetColorStatus(status) {

            if (status === "DISPONIBLE") {
                return "available";
            } else if (status === "MANTENIMIENTO") {
                return "maintenance";
            } else if (status === "OCUPADO") {
                return "occupied";
            } else if (status === "LIMPIEZA") {
                return "cleaning";
            } else if (status === "RESIDENCIA") {
                return "residence";
            }
            return "";
        },
        GetColorStatus(status, ro) {
            if (status === "DISPONIBLE" && ro.is_clean != 1) {
                return "#43a047";
            } else if (status === "MANTENIMIENTO" && ro.is_clean != 1) {
                return "#fbc02d";
            } else if (status === "OCUPADO" && ro.is_clean != 1) {
                return "#e53935";
            } else if (status === "LIMPIEZA" || ro.is_clean == 1) {
                return "#1e88e5";
            } else if (status === "RESIDENCIA") {
                return "#9e9e9e";
            }
            return "";
        },
        ShowDialogExtendTimeRoom(room) {
            this.roomToExtend = room
            if(this.roomToExtend){
                this.openDialogExtendTimeRoom = true
            }
        },
        onRefresh() {
            this.searchRooms()
        },
        clickExport() {
            this.showExportDialog = true;
        },
        clickChangeEstablishment(establishment_id){
            this.loading = true;
            const payload = {
                establishment_id: establishment_id,
            };
            this.$http
                .post(`/hotels/reception/change-user-establishment`, payload)
                .then((response) => {
                    this.$message({
                        type: "success",
                        message: response.data.message,
                    });
                    location.reload();
                })
                .finally(() => (this.loading = false));
        },
        openObservationsModal(room) {
            this.currentRent = {
                id: room.rent.id,
                observations: room.rent.observations || ''
            };
            this.showObservationsModal = true;
        },
        closeObservationsModal() {
            if (this.savingObservations) return;
            this.showObservationsModal = false;
            this.currentRent = {
                id: null,
                observations: ''
            };
            this.savingObservations = false;
        },
        saveObservations() {
            if (!this.currentRent.id) {
                this.$message.error('Error al guardar las observaciones');
                return;
            }

            this.savingObservations = true;
            
            this.$http.post(`/hotels/reception/${this.currentRent.id}/rent/update-observations`, {
                observations: this.currentRent.observations
            })
            .then(response => {
                if (response.data.success) {
                    this.$message.success('Observaciones guardadas correctamente');
                    this.closeObservationsModal();
                    this.showObservationsModal = false;
                    this.searchRooms(); // Refrescar la lista de habitaciones
                } else {
                    throw new Error(response.data.message || 'Error al guardar las observaciones');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const errorMessage = (error.response && error.response.data && error.response.data.message) 
                    ? error.response.data.message 
                    : 'Error al guardar las observaciones';
                this.$message.error(errorMessage);
            })
            .finally(() => {
                this.savingObservations = false;
            });
        },
        formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
        },
        async handleDeleteRent(room) {
            try {
                if (!room.rent || !room.rent.id) {
                    this.$message.error('No se pudo encontrar el ID de la reserva');
                    return;
                }
                
                await this.$confirm('¿Está seguro de eliminar esta reserva? Esta acción no se puede deshacer.', 'Confirmar eliminación', {
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    type: 'warning',
                    confirmButtonClass: 'el-button--danger',
                    cancelButtonClass: 'el-button--default',
                    center: true
                });
                
                // Show loading
                const loading = this.$loading({
                    lock: true,
                    text: 'Eliminando reserva...',
                    spinner: 'el-icon-loading',
                    background: 'rgba(0, 0, 0, 0.7)'
                });
                
                try {
                    await this.$http.delete(`/hotels/bookings/destroy/${room.rent.id}`);
                    
                    this.$message({
                        type: 'success',
                        message: 'Reserva eliminada correctamente',
                        showClose: true
                    });
                    
                    // Refresh the room list
                    this.onRefresh();
                } catch (error) {
                    console.error('Error deleting rent:', error);
                    const message = error.response && error.response.data && error.response.data.message 
                        ? error.response.data.message 
                        : 'Error al eliminar la reserva';
                    
                    this.$message({
                        type: 'error',
                        message: message,
                        showClose: true,
                        duration: 5000
                    });
                } finally {
                    loading.close();
                }
                
            } catch (error) {
                // User cancelled the delete action
                if (error !== 'cancel') {
                    console.error('Error in delete confirmation:', error);
                }
            }
        },
        getTimeRemaining(outputDate, outputTime) {
            if (!outputDate || !outputTime) return { time: '--:--:--:--', isExpired: false };
            
            try {
                // Parsear la fecha en formato Y-m-d y la hora
                const [year, month, day] = outputDate.split('-').map(Number);
                const [hours, minutes] = outputTime.split(':').map(Number);
                
                // Crear la fecha de salida (los meses en JavaScript van de 0 a 11)
                const outputDateTime = new Date(year, month - 1, day, hours, minutes, 0);
                
                // Usar currentTime que se actualiza cada segundo
                const now = this.currentTime;
                
                // Calcular la diferencia en milisegundos
                const diffMs = outputDateTime - now;
                
                // Si la diferencia es negativa, el tiempo ya pasó
                if (diffMs <= 0) return { time: '00   00   00   00', isExpired: true };
                
                // Calcular días, horas, minutos y segundos restantes
                const diffSecs = Math.floor(diffMs / 1000);
                
                // Calcular días
                const days = Math.floor(diffSecs / 86400);
                const remainingSecsAfterDays = diffSecs % 86400;
                
                // Calcular horas
                const hoursRemaining = Math.floor(remainingSecsAfterDays / 3600);
                const remainingSecsAfterHours = remainingSecsAfterDays % 3600;
                
                // Calcular minutos
                const minutesRemaining = Math.floor(remainingSecsAfterHours / 60);
                const secondsRemaining = remainingSecsAfterHours % 60;
                
                // Formatear a DD   HH   MM   SS (con 3 espacios entre cada componente)
                const timeString = [
                    days.toString().padStart(2, '0'),
                    hoursRemaining.toString().padStart(2, '0'),
                    minutesRemaining.toString().padStart(2, '0'),
                    secondsRemaining.toString().padStart(2, '0')
                ].join('   ');
                
                return {
                    time: timeString,
                    isExpired: false
                };
                
            } catch (error) {
                console.error('Error al calcular el tiempo restante:', error);
                return { time: '--:--:--:--', isExpired: false };
            }
        },
    },
};
</script>
