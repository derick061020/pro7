<template>
    <div>
        <div class="page-header pr-0">
            <h2>
                <a href="/hotels/reception">
                    <svg  xmlns="http://www.w3.org/2000/svg" style="margin-top: -5px;" width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-building-skyscraper"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l18 0" /><path d="M5 21v-14l8 -4v18" /><path d="M19 21v-10l-6 -4" /><path d="M9 9l0 .01" /><path d="M9 12l0 .01" /><path d="M9 15l0 .01" /><path d="M9 18l0 .01" /></svg>
                </a>
            </h2>
            <ol class="breadcrumbs">
                <li class="active"><span>CHECK-OUT: <b>{{ title }}</b> (Registro de salida)</span></li>
            </ol>
            <div class="right-wrapper pull-right">
                <div class="btn-group flex-wrap">
                    <button
                        class="btn btn-custom btn-sm mt-2 mr-2"
                        type="button"
                        @click="onGotoBack"
                    >
                        <i class="fa fa-arrow-left"></i> Atras
                    </button>
                </div>
            </div>
        </div>
        <div class="card mb-0 tab-content-default row-new">
            <template v-if="canMakePayment">
                <div class="card-body">
                    <div class="row card-body">
                        <div class="col-12 col-md-3 h1 m-0 pt-1">
                            Salida 
                            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-door-exit" style="transform: translateY(-4px);"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M13 12v.01"></path><path d="M3 21h18"></path><path d="M5 21v-16a2 2 0 0 1 2 -2h7.5m2.5 10.5v7.5"></path><path d="M14 7h7m-3 -3l3 3l-3 3"></path></svg> 
                        </div>
                        <div class="col-7">
                            <span class="text-muted"><svg  xmlns="http://www.w3.org/2000/svg"  width="16"  height="16"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-user"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /></svg> Cliente</span>
                            <h4 class="mt-0"><b>
                                {{ currentRent.customer.name }}</b>
                            </h4>
                        </div>
                        <div class="col-2">
                            <span class="text-muted"> Toallas</span>
                            <h4 class="mt-0"><b>
                                {{ currentRent.towels }}</b>
                            </h4>
                        </div>
                        <div class="col-12 col-md-3 card card-body bg-light-color my-0 mx-1 p-3">
                            <span class="text-muted"><svg  xmlns="http://www.w3.org/2000/svg"  width="16"  height="16"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-id"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 4m0 3a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v10a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3z" /><path d="M9 10m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M15 8l2 0" /><path d="M15 12l2 0" /><path d="M7 16l10 0" /></svg> DNI/RUC/CE</span>
                            <h4 class="m-0"><b>
                                {{ currentRent.customer.number }}</b>
                            </h4>
                        </div>
                        <div class="col-12 col-md-3 card card-body bg-light-color my-0 mx-1 p-3">
                            <span class="text-muted"><svg  xmlns="http://www.w3.org/2000/svg"  width="16"  height="16"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-door"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 12v.01" /><path d="M3 21h18" /><path d="M6 21v-16a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v16" /></svg> {{ currentRent.room.category.description }}</span>
                            <h4 class="m-0"><b>
                                {{ currentRent.room.name }}</b></h4>
                        </div>
                        <div class="col-12 col-md-3 card card-body bg-light-color my-0 mx-1 p-3 position-relative">
                            <el-button v-if="JSON.parse(currentRent.history).length == 1" type="primary" size="mini" icon="el-icon-edit" circle class="position-absolute" style="right: 5px; top: 5px;" @click="showEditDates = true"></el-button>
                            <span class="text-muted"><svg  xmlns="http://www.w3.org/2000/svg"  width="16"  height="16"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-calendar-up"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12.5 21h-6.5a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v5" /><path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h16" /><path d="M19 22v-6" /><path d="M22 19l-3 -3l-3 3" /></svg> Check-IN</span>
                            <h4 class="m-0"><b>
                                {{ new Date(new Date(currentRent.input_date).setDate(new Date(currentRent.input_date).getDate() + 1)).toLocaleDateString('es-ES', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' }).replace(/ de /g, ' ')  }} <br>
                                {{ new Date(`2000-01-01T${currentRent.input_time}`).toLocaleTimeString('es-ES', { hour: 'numeric', minute: '2-digit', hour12: true }) }}</b></h4>
                        </div>
                        <div class="col-12 col-md-3 card card-body bg-light-color my-0 mx-1 p-3">
                            <span class="text-muted"><svg  xmlns="http://www.w3.org/2000/svg"  width="16"  height="16"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-calendar-down"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12.5 21h-6.5a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v5" /><path d="M19 16v6" /><path d="M22 19l-3 3l-3 -3" /><path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h16" /></svg> Check-OUT</span>
                            <h4 class="m-0"><b>
                                {{ new Date(new Date(currentRent.output_date).setDate(new Date(currentRent.output_date).getDate() + 1)).toLocaleDateString('es-ES', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' }).replace(/ de /g, ' ')  }} <br>
                                {{ new Date(`2000-01-01T${currentRent.output_time}`).toLocaleTimeString('es-ES', { hour: 'numeric', minute: '2-digit', hour12: true }) }}</b></h4>
                        </div>

                        <!-- Simple Edit Dates Modal -->
                        <el-dialog
                            title="Editar Fechas"
                            :visible.sync="showEditDates"
                            width="400px">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label>Fecha de Ingreso</label>
                                    <el-date-picker
                                        v-model="editForm.input_date"
                                        type="date"
                                        placeholder="Seleccionar fecha"
                                        format="dd/MM/yyyy"
                                        value-format="yyyy-MM-dd"
                                        style="width: 100%">
                                    </el-date-picker>
                                </div>
                                <div class="col-12 mb-3">
                                    <label>Hora de Ingreso</label>
                                    <el-time-picker
                                        v-model="editForm.input_time"
                                        format="HH:mm"
                                        value-format="HH:mm"
                                        placeholder="Hora"
                                        style="width: 100%">
                                    </el-time-picker>
                                </div>
                                <div class="col-12 mb-3">
                                    <label>Fecha de Salida</label>
                                    <el-date-picker
                                        v-model="editForm.output_date"
                                        type="date"
                                        placeholder="Seleccionar fecha"
                                        format="dd/MM/yyyy"
                                        value-format="yyyy-MM-dd"
                                        :picker-options="{
                                            disabledDate: (time) => {
                                                return time < new Date(editForm.input_date);
                                            }
                                        }"
                                        style="width: 100%">
                                    </el-date-picker>
                                </div>
                                <div class="col-12 mb-3">
                                    <label>Hora de Salida</label>
                                    <el-time-picker
                                        v-model="editForm.output_time"
                                        format="HH:mm"
                                        value-format="HH:mm"
                                        placeholder="Hora"
                                        style="width: 100%">
                                    </el-time-picker>
                                </div>
                            </div>
                            <span slot="footer" class="dialog-footer">
                                <el-button @click="showEditDates = false">Cancelar</el-button>
                                <el-button type="primary" @click="saveDates" :loading="savingDates">Guardar</el-button>
                            </span>
                        </el-dialog>
                    </div>
                    <div class="row card-body">
                        <div class="col-12">
                            <div class="table-responsive">
                                <table class="table text-right">
                                    <tbody>
                                    <tr class="text-left">
                                        <td colspan="6"><b class="h6">
                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-coins"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 14c0 1.657 2.686 3 6 3s6 -1.343 6 -3s-2.686 -3 -6 -3s-6 1.343 -6 3z" /><path d="M9 14v4c0 1.656 2.686 3 6 3s6 -1.344 6 -3v-4" /><path d="M3 6c0 1.072 1.144 2.062 3 2.598s4.144 .536 6 0c1.856 -.536 3 -1.526 3 -2.598c0 -1.072 -1.144 -2.062 -3 -2.598s-4.144 -.536 -6 0c-1.856 .536 -3 1.526 -3 2.598z" /><path d="M3 6v10c0 .888 .772 1.45 2 2" /><path d="M3 11c0 .888 .772 1.45 2 2" /></svg> Alojamiento: Tarifas y Cargos Adicionales</b></td>
                                    </tr>
                                    <tr class="bg-light-color">
                                        <td>#</td>
                                        <td class="text-left">Nombre</td>
                                        <td>Cant. </td>
                                        <td>Precio unitario</td>
                                        <td>Cargo por salir tarde</td>
                                        <td>Comprobante</td>
                                        <td>Total</td>
                                    </tr>
                                    <tr  v-for="(it, i) in JSON.parse(currentRent.history)" 
                                    :key="i"  v-if="!it.hidden">
                                        <td>{{ i + 1 }}</td>
                                        <td class="text-left">{{ it.item.name }}</td>
                                        <td>{{ it.quantity  }}</td>
                                        <td>{{ it.unit_price }}</td>
                                        <td class="float-right">
                                            <div class="d-d-inline-block"
                                                style="max-width: 120px">
                                                <el-input v-if="i == 0" v-model="arrears"
                                                        type="number"></el-input>
                                            </div>
                                        </td>
                                        <td>{{ i== 0 ? room.document : '-' }}</td>
                                        <td class="float-right">
                                            <div class="d-d-inline-block"
                                                style="max-width: 120px">
                                                <el-input
                                                    v-model="it.total"
                                                    readonly
                                                    type="number"
                                                ></el-input>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="text-left" v-if="rentPaidItems.length > 0">
                                        <td colspan="6"><b class="h6">
                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-receipt-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16l-3 -2l-2 2l-2 -2l-2 2l-2 -2l-3 2" /><path d="M14 8h-2.5a1.5 1.5 0 0 0 0 3h1a1.5 1.5 0 0 1 0 3h-2.5m2 0v1.5m0 -9v1.5" /></svg> Servicio a la habitación (Pagado)</b></td>
                                    </tr>
                                    <tr class="bg-light-color" v-if="rentPaidItems.length > 0">
                                        <td>#</td>
                                        <td class="text-left">Descripción</td>
                                        <td>Precio unitario</td>
                                        <td>Cantidad</td>
                                        <td>Comprobante</td>
                                        <td>Total</td>
                                    </tr>
                                    <tr
                                        v-for="(it, i) in rentPaidItems"
                                        :key="i"
                                    >
                                        <td>{{ i + 1 }}</td>
                                        <td class="text-left">{{ it.item.item.description }}</td>
                                        <td>{{ it.item.input_unit_price_value | toDecimals }}</td>
                                        <td>{{ it.item.quantity | toDecimals }}</td>
                                        <td>{{ it.document}}</td>
                                        <td>{{ it.item.total | toDecimals }}</td>
                                    </tr>
                                    <tr><td></td></tr>
                                    <tr class="text-left" v-if="rentDebtItems.length > 0">
                                        <td colspan="6"><b class="h6 ">
                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-receipt-off"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 21v-16m2 -2h10a2 2 0 0 1 2 2v10m0 4.01v1.99l-3 -2l-2 2l-2 -2l-2 2l-2 -2l-3 2" /><path d="M11 7l4 0" /><path d="M9 11l2 0" /><path d="M13 15l2 0" /><path d="M15 11l0 .01" /><path d="M3 3l18 18" /></svg> Servicio a la habitación </b></td>
                                    </tr>
                                    <tr class="bg-light-color" v-if="rentDebtItems.length > 0">
                                        <td>#</td>
                                        <td class="text-left">Descripción</td>
                                        <td>Precio unitario</td>
                                        <td>Cantidad</td>
                                        <td>Estado</td>
                                        <td>Total</td>
                                    </tr>
                                    <tr
                                        v-for="(it, i) in rentDebtItems"
                                        :key="i"
                                    >
                                        <td>{{ i + 1 }}</td>
                                        <td class="text-left">{{ it.item.item.description }}</td>
                                        <td>{{ it.item.input_unit_price_value | toDecimals }}</td>
                                        <td>{{ it.item.quantity | toDecimals }}</td>
                                        <td>
                                            {{ it.payment_status === "PAID" ? "PAGADO" : "CARGADO A LA HABITACION" }}
                                        </td>
                                        <td>{{ it.item.total | toDecimals }}</td>
                                    </tr>
                                    </tbody>
                                    <tfoot style="display:none">
                                    <tr>
                                        <td class="text-right"
                                            colspan="5">Pagado
                                        </td>
                                        <td>
                                            <h3 class="my-0">
                                                <span class="badge badge-pill badge-info">
                                                    {{ totalPaid | toDecimals }}
                                                </span>
                                            </h3>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-right"
                                            colspan="5">Debe
                                        </td>
                                        <td>
                                            <h3 class="my-0">
                                                <span class="badge badge-pill badge-danger">
                                                    {{ totalDebt | toDecimals }}
                                                </span>
                                            </h3>
                                        </td>
                                    </tr>
                                    </tfoot>
                                </table>

                                <!-- Payment History Table -->
                                <table class="table text-right mt-4 w-100">
                                    <tbody>
                                    <tr class="text-left">
                                        <td colspan="4">
                                            <el-button 
                                                type="primary" 
                                                @click="showPaymentSummaryModal = true"
                                                plain
                                                size="small"
                                                icon="el-icon-document"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-history mr-1">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                    <path d="M12 8l0 4l2 2" />
                                                    <path d="M3.05 11a9 9 0 1 1 .5 4m-.5 5v-5h5" />
                                                </svg>
                                                Ver Historial de Pagos
                                            </el-button>
                                            <!-- Botón para abrir el historial de cambios de habitación -->
                                            <el-button 
                                                type="info" 
                                                @click="showRoomHistoryModal = true"
                                                plain
                                                size="small"
                                                class="mt-3"
                                                icon="el-icon-office-building"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-building-community mr-1">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                    <path d="M8 9l5 5v7h-5v-4m0 4h-5v-7l2.5 -2.5l2.5 2.5" />
                                                    <path d="M8 9v-4h8v4" />
                                                    <path d="M19 10a2 2 0 1 0 -2 -2m0 4a2 2 0 1 0 2 -2" />
                                                    <path d="M5 10a2 2 0 1 0 -2 -2m0 4a2 2 0 1 0 2 -2" />
                                                </svg>
                                                Ver Cambios de Habitación
                                            </el-button>
                                        </td>
                                    </tr>
                                    <!-- Add Advance Payment Button -->
                                    
                                    <tr class="font-weight-bold">
                                        <td colspan="3" class="text-right">Total Consumo:</td>
                                        <td class="text-right">{{ calculateTotalConsumption() | toDecimals }}</td>
                                    </tr>
                                    <tr class="font-weight-bold">
                                        <td colspan="3" class="text-right">Total Pagado:</td>
                                        <td class="text-right text-success">{{ calculateTotalPaid() | toDecimals }}</td>
                                    </tr>
                                    <tr v-if="calculateChange() > 0" class="font-weight-bold">
                                        <td colspan="3" class="text-right">Vuelto:</td>
                                        <td class="text-right text-info">{{ calculateChange() | toDecimals }}</td>
                                    </tr>
                                    <tr v-else class="font-weight-bold">
                                        <td colspan="3" class="text-right">Total a Pagar:</td>
                                        <td class="text-right" :class="{ 'text-danger': calculateTotalDebt() > 0 }">
                                            {{ calculateTotalDebt() | toDecimals }}
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                <el-dialog
                    title="Historial de Cambios de Habitación"
                    :visible.sync="showRoomHistoryModal"
                    width="70%"
                    :before-close="() => showRoomHistoryModal = false"
                >
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Cantidad</th>
                                    <th>Fecha</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, index) in JSON.parse(this.currentRent.historial)" :key="index">
                                    <td>{{ index + 1 }}</td>
                                    <td>{{ item.name }}</td>
                                    <td>{{ item.quantity }}</td>
                                    <td>{{ item.date }}</td>
                                    <td>{{ item.total }}</td>
                                    <td  class="text-center">
                                        <el-button
                                            v-if="canDeleteHistoryItem(item) && !index == 0"
                                            type="danger"
                                            size="mini"
                                            icon="el-icon-delete"
                                            @click="confirmDeleteHistoryItem(item.unique_id)"
                                            :loading="deletingHistoryId === item.unique_id"
                                        ></el-button>
                                    </td>
                                </tr>
                                <tr v-if="!JSON.parse(this.currentRent.historial) || JSON.parse(this.currentRent.historial).length === 0">
                                    <td colspan="5" class="text-center">No hay registros de cambios</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <span slot="footer" class="dialog-footer">
                        <el-button @click="showRoomHistoryModal = false">Cerrar</el-button>
                    </span>
                </el-dialog>
                                
                                <!-- Add Advance Payment Dialog -->
                                <el-dialog
                                    title="Agregar Adelanto de Pago"
                                    :visible.sync="showAddAdvanceDialog"
                                    width="50%"
                                    :before-close="() => { showAddAdvanceDialog = false; }"
                                >
                                    <el-form :model="advanceForm" label-width="200px">
                                        <el-form-item label="Método de pago" required>
                                            <el-select
                                                v-model="advanceForm.payment_method_type_id"
                                                class="w-100"
                                                placeholder="Seleccione el método de pago"
                                                filterable
                                            >
                                                <el-option
                                                    v-for="method in paymentMethodTypes"
                                                    :key="method.id"
                                                    :label="method.description"
                                                    :value="method.id"
                                                ></el-option>
                                            </el-select>
                                        </el-form-item>
                                        
                                        <el-form-item label="Destino" required>
                                            <el-select
                                                v-model="advanceForm.payment_destination_id"
                                                class="w-100"
                                                placeholder="Seleccione el destino"
                                                filterable
                                            >
                                                <el-option
                                                    v-for="destination in paymentDestinations"
                                                    :key="destination.id"
                                                    :label="destination.description"
                                                    :value="destination.id"
                                                ></el-option>
                                            </el-select>
                                        </el-form-item>
                                        
                                        <el-form-item label="Monto" required>
                                            <el-input-number
                                                v-model="advanceForm.amount"
                                                :min="0.01"
                                                :precision="2"
                                                class="w-100"
                                                placeholder="Ingrese el monto"
                                            ></el-input-number>
                                        </el-form-item>
                                        
                                        <el-form-item label="Descripción">
                                            <el-input
                                                v-model="advanceForm.description"
                                                placeholder="Ingrese una descripción"
                                            ></el-input>
                                        </el-form-item>
                                    </el-form>
                                    
                                    <span slot="footer" class="dialog-footer">
                                        <el-button @click="showAddAdvanceDialog = false">Cancelar</el-button>
                                        <el-button 
                                            type="primary" 
                                            @click="addAdvancePayment"
                                            :disabled="!advanceForm.payment_method_type_id || !advanceForm.payment_destination_id || !advanceForm.amount"
                                        >
                                            Guardar
                                        </el-button>
                                    </span>
                                </el-dialog>
                            </div>
                        </div>
                    </div>
                    <div class="row card-body">
                        <div class="col-12 h6 m-0">
                            <b><svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-file-dollar"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M14 11h-2.5a1.5 1.5 0 0 0 0 3h1a1.5 1.5 0 0 1 0 3h-2.5" /><path d="M12 17v1m0 -8v1" /></svg> Información del comprobante (Solo para el monto pendiente de pago)</b>
                        </div>
                        <div class="col-lg-3">
                            <div
                                :class="{ 'has-danger': errors.document_type_id }"
                                class="form-group"
                            >
                                <label class="control-label">Tipo comprobante</label>
                                <el-select
                                    v-model="document.document_type_id"
                                    class="border-left rounded-left border-info"
                                    dusk="document_type_id"
                                    popper-class="el-select-document_type"
                                    @change="changeDocumentType"
                                >
                                    <el-option
                                        v-for="option in document_types"
                                        :key="option.id"
                                        :label="option.description"
                                        :value="option.id"
                                    ></el-option>
                                </el-select>
                                <small
                                    v-if="errors.document_type_id"
                                    class="form-control-feedback"
                                    v-text="errors.document_type_id[0]"
                                ></small>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div :class="{ 'has-danger': errors.series_id }"
                                class="form-group">
                                <label class="control-label">Serie</label>
                                <el-select v-model="document.series_id">
                                    <el-option
                                        v-for="option in series"
                                        :key="option.id"
                                        :label="option.number"
                                        :value="option.id"
                                    ></el-option>
                                </el-select>
                                <small
                                    v-if="errors.series_id"
                                    class="form-control-feedback"
                                    v-text="errors.series_id[0]"
                                ></small>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div
                                :class="{ 'has-danger': errors.date_of_issue }"
                                class="form-group"
                            >
                                <label class="control-label">Fecha de emisión</label>
                                <el-date-picker
                                    v-model="document.date_of_issue"
                                    :clearable="false"
                                    readonly
                                    type="date"
                                    value-format="dd-MM-yyyy"
                                    format="dd-MM-yyyy"
                                    @change="changeDateOfIssue"
                                ></el-date-picker>
                                <small
                                    v-if="errors.date_of_issue"
                                    class="form-control-feedback"
                                    v-text="errors.date_of_issue[0]"
                                ></small>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div
                                :class="{ 'has-danger': errors.date_of_issue }"
                                class="form-group"
                            >
                                <label class="control-label">Fecha de vencimiento</label>
                                <el-date-picker
                                    v-model="document.date_of_due"
                                    :clearable="false"
                                    type="date"
                                    value-format="dd-MM-yyyy"
                                    format="dd-MM-yyyy"
                                ></el-date-picker>
                                <small
                                    v-if="errors.date_of_due"
                                    class="form-control-feedback"
                                    v-text="errors.date_of_due[0]"
                                ></small>
                            </div>
                        </div>
                    </div>
                    <div class="row card-body bg-accent-color m-3">
                        <div class="col-12 h6 m-0">
                            <b><svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-cash-register"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M21 15h-2.5c-.398 0 -.779 .158 -1.061 .439c-.281 .281 -.439 .663 -.439 1.061c0 .398 .158 .779 .439 1.061c.281 .281 .663 .439 1.061 .439h1c.398 0 .779 .158 1.061 .439c.281 .281 .439 .663 .439 1.061c0 .398 -.158 .779 -.439 1.061c-.281 .281 -.663 .439 -1.061 .439h-2.5" /><path d="M19 21v1m0 -8v1" /><path d="M13 21h-7c-.53 0 -1.039 -.211 -1.414 -.586c-.375 -.375 -.586 -.884 -.586 -1.414v-10c0 -.53 .211 -1.039 .586 -1.414c.375 -.375 .884 -.586 1.414 -.586h2m12 3.12v-1.12c0 -.53 -.211 -1.039 -.586 -1.414c-.375 -.375 -.884 -.586 -1.414 -.586h-2" /><path d="M16 10v-6c0 -.53 -.211 -1.039 -.586 -1.414c-.375 -.375 -.884 -.586 -1.414 -.586h-4c-.53 0 -1.039 .211 -1.414 .586c-.375 .375 -.586 .884 -.586 1.414v6m8 0h-8m8 0h1m-9 0h-1" /><path d="M8 14v.01" /><path d="M8 17v.01" /><path d="M12 13.99v.01" /><path d="M12 17v.01" /></svg> Registro de pagos pendientes</b>
                        </div>
                        <div class="col-12">
                            <table  style="display:block;" class="table">
                                <thead style="display: table; width: 100%;">
                                <tr style="width: 100%;" width="100%">
                                    <th>M. Pago</th>
                                    <th>Destino</th>
                                    <th>Referencia</th>
                                    <th>Monto</th>
                                    <th>Fecha</th>
                                    <th width="15%">
                                        <a
                                            class="text-center font-weight-bold text-info"
                                            href="#"
                                            @click.prevent="clickAddPayment"
                                        >[+ Agregar]</a>
                                    </th>
                                </tr>
                                </thead>
                                <tbody class="flex" style="width: 100%;">
                                <!-- Current Payments (editable) -->
                                <tr class="row" v-for="(row, index) in document.payments" :key="'current-' + index" :style="{display: row.display}">
                                    <td class="col-12  col-md-2">
                                        <div class="form-group mb-2 mr-2">
                                            <el-select v-model="row.payment_method_type_id">
                                                <el-option
                                                    v-for="option in paymentMethodTypes"
                                                    :key="option.id"
                                                    :label="option.description"
                                                    :value="option.id"
                                                ></el-option>
                                            </el-select>
                                        </div>
                                    </td>
                                    <td class="col-12  col-md-2">
                                        <div class="form-group mb-2 mr-2">
                                            <el-select
                                                v-model="row.payment_destination_id"
                                                :disabled="row.payment_destination_disabled"
                                                filterable
                                            >
                                                <el-option
                                                    v-for="option in paymentDestinations"
                                                    :key="option.id"
                                                    :label="option.description"
                                                    :value="option.id"
                                                ></el-option>
                                            </el-select>
                                        </div>
                                    </td>
                                    <td class="col-12  col-md-2">
                                        <div class="form-group mb-2 mr-2">
                                            <el-input v-model="row.reference"></el-input>
                                        </div>
                                    </td>
                                    <td class="col-12  col-md-2">
                                        <div class="form-group mb-2 mr-2">
                                            <el-input v-model="row.payment"></el-input>
                                        </div>
                                    </td>
                                    <td class="col-12  col-md-2" >
                                        <div class="form-group mb-2 mr-2">
                                            <el-input :value="new Date().toLocaleString('es-ES')" disabled></el-input>
                                        </div>
                                    </td>
                                    <td class="col-12  col-md-2 series-table-actions text-center">
                                        <button
                                            class="btn waves-effect waves-light btn-xs btn-success mr-1"
                                            type="button"
                                            @click.prevent="savePayment(row, index)"
                                            :disabled="!row.payment_method_type_id || !row.payment_destination_id || !row.payment"
                                            title="Guardar pago"
                                        >
                                            <i class="fa fa-save"></i>
                                        </button>
                                        <button
                                            class="btn waves-effect waves-light btn-xs btn-danger"
                                            type="button"
                                            @click.prevent="clickCancel(index)"
                                            title="Eliminar"
                                        >
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                
                                
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="row card-body">
                        <div class="col-12 pt-3 text-right">
                            <template v-if="canMakePayment && totalDebt > 0">
                                <el-button
                                    :disabled="loading || calculateChange() > 0"
                                    :loading="loading"
                                    class="btn btn-primary"
                                    @click="showCheckoutModal"
                                >
                                <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-device-floppy"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" /><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M14 4l0 4l-6 0l0 -4" /></svg> Finalizar salida
                                </el-button>
                                <el-button
                                    v-if="calculateChange() > 0"
                                    type="warning"
                                    @click="handleReturnChange"
                                    :loading="loading"
                                >
                                    <i class="fa fa-exchange"></i> Devolver Vuelto (S/. {{ calculateChange().toFixed(2) }})
                                </el-button>
                            </template>
                            <template v-else-if="canMakePayment">
                                <el-button
                                    :disabled="loading"
                                    :loading="loading"
                                    class="btn btn-primary"
                                    @click="onGoToFinalizeRent"
                                >
                                <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-device-floppy"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" /><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M14 4l0 4l-6 0l0 -4" /></svg> Guardar
                                </el-button>
                            </template>
                        </div>
                    </div>
                </div>     
            </template>
            <template v-else>
                <div class="card text-center">
                   <div>
                    <svg  xmlns="http://www.w3.org/2000/svg"  width="96"  height="96"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="1.5"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-rosette-discount-check text-success"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 7.2a2.2 2.2 0 0 1 2.2 -2.2h1a2.2 2.2 0 0 0 1.55 -.64l.7 -.7a2.2 2.2 0 0 1 3.12 0l.7 .7c.412 .41 .97 .64 1.55 .64h1a2.2 2.2 0 0 1 2.2 2.2v1c0 .58 .23 1.138 .64 1.55l.7 .7a2.2 2.2 0 0 1 0 3.12l-.7 .7a2.2 2.2 0 0 0 -.64 1.55v1a2.2 2.2 0 0 1 -2.2 2.2h-1a2.2 2.2 0 0 0 -1.55 .64l-.7 .7a2.2 2.2 0 0 1 -3.12 0l-.7 -.7a2.2 2.2 0 0 0 -1.55 -.64h-1a2.2 2.2 0 0 1 -2.2 -2.2v-1a2.2 2.2 0 0 0 -.64 -1.55l-.7 -.7a2.2 2.2 0 0 1 0 -3.12l.7 -.7a2.2 2.2 0 0 0 .64 -1.55v-1" /><path d="M9 12l2 2l4 -4" /></svg>
                    <h2>Checkout éxitoso en {{this.currentRent.room.name}}</h2>
                    <p class="text-sm">Ya se finalizó el proceso y puede volver a recepción.</p>
                    <el-button
                        @click="onExitPage"
                        type="primary"
                        class="btn btn-primary mt-4"
                    >
                        <span class="ml-2">
                            Volver a recepción
                        </span>
                    </el-button>
                </div>
                   
                </div>
            </template>
        </div>
        <!-- Modal de Resumen de Pagos -->
        <el-dialog
            title="Resumen de Pagos"
            :visible.sync="showPaymentSummaryModal"
            width="80%"
            :close-on-click-modal="false"
            top="5vh"
            custom-class="payment-summary-dialog"
        >
            <div class="table-responsive" style="max-height: 60vh; overflow-y: auto;">
                <table class="table table-bordered table-hover">
                    <thead class="bg-light" style="position: sticky; top: 0; z-index: 1; background-color: #f8f9fa;">
                        <tr>
                            <th width="5%" class="text-center">#</th>
                            <th class="text-left">Descripción</th>
                            <th class="text-center" width="20%">Tipo de Pago</th>
                            <th class="text-right" width="15%">Monto</th>
                            <th class="text-center" width="15%">Fecha y Hora</th>
                            <th class="text-center" width="10%">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!currentRent.payment_history || currentRent.payment_history.length === 0">
                            <td colspan="5" class="text-center py-4">
                                <i class="el-icon-info mr-1"></i> No hay registros de pago
                            </td>
                        </tr>
                        <tr v-else v-for="(item, index) in JSON.parse(currentRent.payment_history)" :key="index" class="payment-row">
                            <td class="text-center align-middle">{{ index + 1 }}</td>
                            <td class="align-middle">
                                <div class="d-flex align-items-center">
                                    <i v-if="item.amount < 0" class="el-icon-refresh-left text-warning mr-2"></i>
                                    <i v-else class="el-icon-circle-check text-success mr-2"></i>
                                    {{ item.description || 'Pago registrado' }}
                                </div>
                            </td>
                            <td class="text-center align-middle">
                                <span class="badge badge-pill" :class="getPaymentMethodClass(item.payment_method_type_id)">
                                    {{ getPaymentMethodDescription(item.payment_method_type_id) }}
                                </span>
                            </td>
                            <td class="text-right align-middle" :class="{ 'text-success': item.amount >= 0, 'text-danger': item.amount < 0 }">
                                <strong>{{ (item.amount >= 0 ? '+' : '') + (item.amount | toDecimals) }}</strong>
                            </td>
                            <td class="text-center align-middle">
                                <small class="text-muted">{{ item.date }}</small>
                            </td>
                            <td class="text-center align-middle" width="10%">
                                <el-button 
                                    v-if="item.amount > 0"
                                    type="text" 
                                    size="mini" 
                                    @click="openChangePaymentMethodDialog(item, index)"
                                    title="Cambiar tipo de pago"
                                >
                                    <i class="el-icon-edit"></i>
                                </el-button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <span slot="footer" class="dialog-footer">
                <el-button @click="showPaymentSummaryModal = false">Cerrar</el-button>
            </span>
        </el-dialog>

        <!-- Dialogo para cambiar método de pago -->
        <el-dialog
            title="Cambiar Método de Pago"
            :visible.sync="showChangePaymentMethodDialog"
            width="400px"
            :close-on-click-modal="false"
        >
            <el-form :model="paymentChangeForm" label-position="top">
                <el-form-item label="Nuevo Método de Pago" required>
                    <el-select v-model="paymentChangeForm.new_payment_method_type_id" class="w-100">
                        <el-option
                            v-for="option in paymentMethodTypes"
                            :key="option.id"
                            :label="option.description"
                            :value="option.id"
                        ></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="Observación" prop="observation">
                    <el-input
                        v-model="paymentChangeForm.observation"
                        type="textarea"
                        :rows="2"
                        placeholder="Ingrese una observación (opcional)"
                    ></el-input>
                </el-form-item>
            </el-form>
            <span slot="footer" class="dialog-footer">
                <el-button @click="showChangePaymentMethodDialog = false">Cancelar</el-button>
                <el-button 
                    type="primary" 
                    @click="confirmChangePaymentMethod"
                    :loading="loadingPaymentMethodChange"
                >
                    Guardar Cambios
                </el-button>
            </span>
        </el-dialog>

        <document-options
            :isContingency="false"
            :recordId="documentNewId"
            :showClose="true"
            :showDialog.sync="showDialogDocumentOptions"
        ></document-options>

        <sale-note-options :configuration="config"
                           :recordId="documentNewId"
                           :showClose="true"
                           :showDialog.sync="showDialogSaleNoteOptions">
        </sale-note-options>

            <!-- Modal de selección de ítems -->
        <el-dialog
            title="Seleccionar ítems para comprobante"
            :visible.sync="showHistorySelection"
            width="80%"
            :close-on-click-modal="false"
            :show-close="true"
            :close-on-press-escape="false"
        >
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th width="50">
                                <el-checkbox
                                    :value="historyItems.length > 0 && historyItems.every(i => i.selected)"
                                    @change="toggleSelectAll"
                                ></el-checkbox>
                            </th>
                            <th>Descripción</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in historyItems" :key="item.id">
                            <td>
                                <el-checkbox v-model="item.selected" @change="updateSelectedItems"></el-checkbox>
                            </td>
                            <td>{{ (item.name_product_pdf || item.item.description) || 'N/A' }}</td>
                            <td>{{ item.quantity }}</td>
                            <td>{{ item.unit_price | toDecimals }}</td>
                            <td>{{ (item.quantity * item.unit_price) | toDecimals }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <span slot="footer" class="dialog-footer">
                <el-button @click="showHistorySelection = false">Cancelar</el-button>
                <el-button type="primary" @click="confirmCheckout">Continuar con la salida</el-button>
            </span>
        </el-dialog>
    </div>
</template>

<script>
import moment from "moment";
import DocumentOptions from "@views/documents/partials/options.vue";
import SaleNoteOptions from "@views/sale_notes/partials/options.vue";
import {calculateRowItem} from "../../../../../../../resources/js/helpers/functions";
import {exchangeRate, functions} from "../../../../../../../resources/js/mixins/functions";
import {mapActions, mapState} from "vuex/dist/vuex.mjs";

export default {
    components: {
        DocumentOptions,
        SaleNoteOptions,
    },
    mixins: [
        exchangeRate,
        functions
    ],
    props: {
        rent: {
            type: Object,
            required: true,
        },
        customer: {
            type: Object,
            required: true,
        },
        room: {
            type: Object,
            required: true,
        },
        paymentMethodTypes: {
            type: Array,
            required: true,
        },
        paymentDestinations: {
            type: Array,
            required: true,
        },
        allSeries: {
            type: Array,
            required: true,
        },
        documentTypesInvoice: {
            type: Array,
            required: true,
        },
        configuration: {
            type: Object,
            required: false,
        },
        affectationIgvTypes: {
            type: Array,
            required: true,
        },
        payments: {
            type: Array,
            required: true,
        },
        rentItems: {
            type: Array,
            required: true,
        },
    },
    computed: {
        ...mapState({
            config: state => state.config,
            storePaymentMethodTypes: state => state.paymentMethodTypes,
            storePaymentDestinations: state => state.paymentDestinations,
            payment_destinations: state => state.payment_destinations
        }),
        
        paymentHistory() {
            if (!this.currentRent || !this.currentRent.payment_history) return [];
            try {
                const history = JSON.parse(this.currentRent.payment_history);
                return Array.isArray(history) ? history : [];
            } catch (e) {
                console.error('Error parsing payment history:', e);
                return [];
            }
        },
        
        allPayments() {
            // Combine current payments with history for display
            const currentPayments = this.document.payments.map(p => ({
                ...p,
                isCurrent: true,
                date: new Date().toLocaleString('es-ES')
            }));
            
            const historyPayments = this.paymentHistory.map(p => ({
                ...p,
                isCurrent: false
            }));
            
            return [...currentPayments, ...historyPayments];
        },
        
        // Helper to get payment method description
        getPaymentMethodDescription() {
            return (paymentMethodId) => {
                if (!paymentMethodId) return '-';
                // Use prop first, fallback to store
                const methods = this.paymentMethodTypes || this.storePaymentMethodTypes || [];
                const method = methods.find(m => m.id === paymentMethodId);
                return method ? method.description : paymentMethodId;
            };
        },
        
        // Helper to get payment destination description
        getPaymentDestinationDescription() {
            return (destinationId) => {
                if (!destinationId) return '-';
                // Use prop first, fallback to store
                const destinations = this.paymentDestinations || this.storePaymentDestinations || [];
                const destination = destinations.find(d => d.id === destinationId);
                return destination ? destination.description : destinationId;
            };
        },
        
        // Format date for display
        formatDate() {
            return (dateString) => {
                if (!dateString) return '-';
                try {
                    const date = new Date(dateString);
                    return date.toLocaleString('es-ES', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                } catch (e) {
                    console.error('Error formatting date:', e);
                    return dateString;
                }
            };
        },
        canMakePayment: function () {
            if (
                this.currentRent !== undefined &&
                this.currentRent.status !== undefined &&
                this.currentRent.status !== 'FINALIZADO'
            ) {
                return true;
            }
            return false;
        },
        hasDebt()
        {
            return this.totalDebt > 0
        },
        rentPaidItems()
        {
            return this.rentItems.filter(it => it.type === 'PRO' && it.payment_status === 'PAID')
        },
        rentDebtItems()
        {
            return this.rentItems.filter(it => it.type === 'PRO' && it.payment_status === 'DEBT')
        }
    },
    created() {
        this.loadConfiguration();
        this.$store.commit('setConfiguration', this.configuration);
        this.currentRent = this.rent

    },
    data() {
        return {
            showHistorySelection: false,
            selectedHistoryItems: [],
            showChangePaymentMethodDialog: false,
            loadingPaymentMethodChange: false,
            paymentChangeForm: {
                payment_index: null,
                payment_id: null,
                new_payment_method_type_id: null,
                observation: ''
            },
            showRoomHistoryModal: false,
            historyItems: [],
            showPaymentSummaryModal: false,
            title: "",
            currentRent: {
                items: [],
                payments: [],
                history: [],
                payment_history: [],
                total_without_rounding: 0
            },
            arrears: 0,
            total: 0,
            debtRoom: 0,
            loading: false,
            totalPaid: 0,
            totalDebt: 0,
            response: {},
            document: {
                payments: [],
            },
            errors: {},
            series: [],
            document_types: [],
            all_document_types: [],
            resource_documents: "documents",
            showDialogDocumentOptions: false,
            documentNewId: null,
            form_cash_document: {},
            showDialogSaleNoteOptions: false,
            form: {
                establishment_id: null,
                date_of_issue: null
            },
            deletingHistoryId: null,
            // Advance payment dialog
            showAddAdvanceDialog: false,
            showEditDates: false,
            savingDates: false,
            editForm: {
                input_date: null,
                input_time: null,
                output_date: null,
                output_time: null
            },
            advanceForm: {
                payment_method_type_id: null,
                payment_destination_id: null,
                type: 'advancePayment',
                amount: 0,
                description: ''
            }
        };
    },
    async mounted() {
        // Inicializar el formulario de edición con los valores actuales
        if (this.currentRent) {
            this.editForm = {
                input_date: this.currentRent.input_date,
                input_time: this.currentRent.input_time,
                output_date: this.currentRent.output_date,
                output_time: this.currentRent.output_time
            };
        }
        // console.log(this.config);

        this.form.establishment_id = this.config.establishment.id;
        this.form.date_of_issue = moment().format("YYYY-MM-DD");
        await this.getPercentageIgv();

        this.room.item = await calculateRowItem(this.room.item, "PEN", 3, this.percentage_igv);

        this.initForm();
        await this.initDocument();
        this.all_document_types = this.documentTypesInvoice;
        this.title = this.currentRent.room.name;
        this.total = this.room.item.total;

        this.document.items = await this.currentRent.items
            .filter(it => it.payment_status === 'DEBT')
            .map(i => {
                if (!i.item.affectation_igv_type || _.isEmpty(i.item.affectation_igv_type)) {
                    i.item.affectation_igv_type = _.find(this.affectationIgvTypes, { id: i.item.affectation_igv_type_id });
                }
                return calculateRowItem(i.item, "PEN", 3, this.percentage_igv);
            });

        await this.onCalculateTotals();
        await this.onCalculatePaidAndDebts();

        
            let cash = _.find(this.paymentDestinations, {id: 'cash'})
            let groupedPayments = _.groupBy(JSON.parse(this.currentRent.payment_history), 'payment_method_type_id');
            let payments = Object.keys(groupedPayments).map(key => {
                let sum = groupedPayments[key].reduce((total, item) => total + item.amount, 0);
                let first = groupedPayments[key][0];
                return {
                    id: null,
                    document_id: null,
                    date_of_payment: moment().format("YYYY-MM-DD"),
                    payment_method_type_id: key,
                    payment_destination_id: first.payment_destination_id,
                    reference: first.reference,
                    payment: sum,
                    display: 'none'
                }
            });
            this.document.payments = payments;
        if(this.calculateTotalDebt() > 0){
            
            this.document.payments.push({
                id: null,
                document_id: null,
                date_of_payment: moment().format("YYYY-MM-DD"),
                payment_method_type_id: "01",
                payment_destination_id: (cash)? cash.id : null,
                reference: "", 
                payment: this.calculateTotalDebt(),
                display: 'flex'
            });
        }
        
        this.validateIdentityDocumentType();
        const date = moment().format("YYYY-MM-DD");
        await this.searchExchangeRateByDate(date).then((res) => {
            this.document.exchange_rate_sale = res;
        });
    },
    watch: {
        arrears(value) {
            console.log('arrears');
            if (isNaN(value)) {
                return;
            }
            if (value >= 0) {
                const total = parseFloat(this.room.item.total) + parseFloat(value);
                this.total = total;
                this.onCalculatePaidAndDebts();
            }
        },
    },
    methods: {
            // Métodos para cambiar el método de pago
            openChangePaymentMethodDialog(payment, index) {
                this.paymentChangeForm = {
                    payment_index: index,
                    payment_id: payment.id,
                    new_payment_method_type_id: payment.payment_method_type_id,
                    observation: ''
                };
                this.showChangePaymentMethodDialog = true;
            },
            
            async confirmChangePaymentMethod() {
                if (!this.paymentChangeForm.new_payment_method_type_id) {
                    this.$message.error('Por favor seleccione un método de pago');
                    return;
                }
                
                this.loadingPaymentMethodChange = true;
                
                try {
                    const paymentHistory = JSON.parse(this.currentRent.payment_history);
                    const paymentToUpdate = paymentHistory[this.paymentChangeForm.payment_index];
                    
                    // Guardar el método de pago anterior para el historial
                    const oldPaymentMethod = paymentToUpdate.payment_method_type_id;
                    
                    // Actualizar el método de pago
                    paymentToUpdate.payment_method_type_id = this.paymentChangeForm.new_payment_method_type_id;
                    
                    // Agregar observación si existe
                    if (this.paymentChangeForm.observation) {
                        paymentToUpdate.observation = this.paymentChangeForm.observation;
                    }
                    
                    // Actualizar en el servidor
                    await this.$http.post(`/hotel/rents/${this.currentRent.id}/update-payment-method`, {
                        payment_index: this.paymentChangeForm.payment_index,
                        new_payment_method_type_id: this.paymentChangeForm.new_payment_method_type_id,
                        observation: this.paymentChangeForm.observation,
                        old_payment_method_type_id: oldPaymentMethod
                    });
                    
                    // Actualizar el historial local
                    this.currentRent.payment_history = JSON.stringify(paymentHistory);
                    window.location.reload();
                    this.$message.success('Método de pago actualizado correctamente');
                    this.showChangePaymentMethodDialog = false;
                    
                } catch (error) {
                    console.error('Error al actualizar el método de pago:', error);
                    this.$message.error('Ocurrió un error al actualizar el método de pago');
                } finally {
                    this.loadingPaymentMethodChange = false;
                }
            },

            // Verifica si un ítem del historial puede ser eliminado
            canDeleteHistoryItem(item) {
                // Aquí puedes agregar lógica adicional para determinar si un ítem puede ser eliminado
                // Por ejemplo, solo permitir eliminar ciertos tipos de registros o con ciertos estados
                const history = JSON.parse(this.currentRent.history);
                const found = history.find(h => h.id === item.id && h.quantity > item.quantity);
                return (found && !item.delete) || item.is_product;
            },

            // Muestra un diálogo de confirmación antes de eliminar un registro del historial
            confirmDeleteHistoryItem(historyId) {
                this.$confirm('¿Está seguro de eliminar este registro del historial?', 'Confirmar eliminación', {
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    type: 'warning',
                    confirmButtonClass: 'el-button--danger',
                    cancelButtonClass: 'el-button--default',
                    showClose: true
                }).then(() => {
                    this.deleteHistoryRecord(historyId);
                }).catch(() => {});
            },

            // Elimina un registro del historial mediante una petición al servidor
            async deleteHistoryRecord(historyId) {
                try {
                    this.deletingHistoryId = historyId;
                    const response = await this.$http.delete(`/hotels/reception/rents/${this.currentRent.id}/history/${historyId}`);
                    
                    if (response.data.success) {
                        // Actualizar el historial en el objeto currentRent
                        this.currentRent.historial = JSON.stringify(response.data.historial);
                        this.$message.success('Registro eliminado correctamente');
                    location.reload();
                    
                        
                    } else {
                        this.$message.error(response.data.message || 'Error al eliminar el registro');
                    }
                } catch (error) {
                    console.error('Error al eliminar el registro del historial:', error);
                    this.$message.error('Error al eliminar el registro del historial');
                } finally {
                    this.deletingHistoryId = null;
                }
            },

            loadHistoryItems() {
                this.historyItems = JSON.parse(this.currentRent.history || '[]').map((item, index) => ({
                    ...item,
                    id: index,
                    selected: true
                }));
                this.selectedHistoryItems = [...this.historyItems];
            },
            
            toggleSelectAll(selected) {
                this.historyItems = this.historyItems.map(item => ({
                    ...item,
                    selected
                }));
                this.updateSelectedItems();
            },
            
            updateSelectedItems() {
                this.selectedHistoryItems = this.historyItems.filter(item => item.selected);
                console.log(this.selectedHistoryItems);
                

            },
            
            confirmCheckout() {
                if (this.selectedHistoryItems.length === 0) {
                    this.$message.warning('Debe seleccionar al menos un ítem para generar el comprobante');
                    return;
                }
                this.showHistorySelection = false;
                this.processCheckout();
            },
            
            processCheckout() {
                // Add selected items to form data
                this.onGoToInvoice();              
                // Submit the form
                this.$refs.formSubmit.submit();
            },
            
            async saveDates() {
                try {
                    this.savingDates = true;
                    const response = await this.$http.post(`/hotels/reception/rents/${this.currentRent.id}/update-dates`, this.editForm);
                    
                    if (response.data.success) {
                        this.currentRent.input_date = this.editForm.input_date;
                        this.currentRent.input_time = this.editForm.input_time;
                        this.currentRent.output_date = this.editForm.output_date;
                        this.currentRent.output_time = this.editForm.output_time;
                        
                        this.$message.success('Fechas actualizadas correctamente');
                        this.showEditDates = false;
                        
                        // Recargar para actualizar cálculos
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    }
                } catch (error) {
                    console.error('Error al actualizar fechas:', error);
                    this.$message.error('Error al actualizar las fechas');
                } finally {
                    this.savingDates = false;
                }
            },
            
            showCheckoutModal() {
                this.loadHistoryItems();
                this.showHistorySelection = true;
            },
        async addAdvancePayment() {
            try {
                // Create a new payment history array with the new payment
                const paymentHistory = this.currentRent.payment_history ? 
                    JSON.parse(this.currentRent.payment_history) : [];
                
                if(!this.advanceForm.payment_destination_id){
                    return this.$message.error('Debe seleccionar un destino de pago');
                }
                
                const newPayment = {
                    id: paymentHistory.length + 1,
                    description: this.advanceForm.description,
                    amount: parseFloat(this.advanceForm.amount),
                    date: new Date().toLocaleString('es-ES'),
                    payment_method_type_id: this.advanceForm.payment_method_type_id,
                    payment_destination_id: this.advanceForm.payment_destination_id
                };
                
                // Add the new payment to the history
                paymentHistory.push(newPayment);
                
                // Update the currentRent with the new payment history
                this.currentRent.payment_history = JSON.stringify(paymentHistory);
                
                // Save the updated rent with the new payment history
                const response = await this.$http.post(`/hotels/rents/${this.currentRent.id}/update-payment-history`, {
                    payment_history: this.currentRent.payment_history
                });
                
                // Refresh the data
                await this.initDocument();
                
                // Close the dialog and reset the form
                this.showAddAdvanceDialog = false;
                this.advanceForm = {
                    payment_method_type_id: null,
                    payment_destination_id: null,
                    amount: 0,
                    description: 'Adelanto de pago'
                };
                
                this.$message.success('Adelanto registrado correctamente');
                
            } catch (error) {
                console.error('Error adding advance payment:', error);
                this.$message.error('Error al registrar el adelanto');
            }
        },
        parseJsonData(data) {
            if (!data) return [];
            if (typeof data === 'string') {
                try {
                    return JSON.parse(data) || [];
                } catch (e) {
                    console.error('Error parsing JSON data:', e);
                    return [];
                }
            }
            return Array.isArray(data) ? data : [];
        },
        calculateTotalConsumption() {
            if (!this.currentRent) return 0;
            const history = this.parseJsonData(this.currentRent.history);
            if (!history.length) return 0;
            console.log(history);
            return history.reduce((total, item) => {
                const amount = item.total || 0;
                return total + (parseFloat(amount) || 0);
            }, 0);
        },
        calculateTotalPaid() {
            if (!this.currentRent) return 0;
            const paymentHistory = this.parseJsonData(this.currentRent.payment_history);
            if (!paymentHistory.length) return 0;
            console.log(paymentHistory);
                
            
            return paymentHistory.reduce((total, payment) => {
                return total + (parseFloat(payment.amount) || 0);
            }, 0);
        },
        calculateTotalDebt() {
            const total = this.calculateTotalConsumption();
            const paid = this.calculateTotalPaid();
            const debt = total - paid;
            return debt > 0 ? debt : 0;
        },
        calculateChange() {
            const total = this.calculateTotalConsumption();
            const paid = this.calculateTotalPaid();
            return paid > total ? paid - total : 0;
        },
        async handleReturnChange() {
            try {
                const change = this.calculateChange();
                if (change <= 0) return;
                
                // Create a payment object for the change return
                const payment = {
                    id: null,
                    document_id: null,
                    date_of_payment: moment().format("YYYY-MM-DD"),
                    payment_method_type_id: "01", // Efectivo
                    payment_destination_id: _.get(_.find(this.paymentDestinations, {id: 'cash'}), 'id'),
                    reference: 'DEVOLUCIÓN DE VUELTO',
                    payment: -change, // Negative payment for return
                    display: ''
                };
                
                // Add to document payments
                this.document.payments.push(payment);
                
                // Save the payment using the same approach as addAdvancePayment
                const paymentHistory = this.parseJsonData(this.currentRent.payment_history || '[]');
                const newPayment = {
                    id: Date.now().toString(),
                    date: moment().format('YYYY-MM-DD HH:mm:ss'),
                    amount: -change,
                    type: 'advancePayment',
                    description: 'Devolución de vuelto',
                    payment_method_type_id: payment.payment_method_type_id,
                    payment_destination_id: payment.payment_destination_id,
                    reference: payment.reference
                };
                
                paymentHistory.push(newPayment);
                
                // Update the currentRent with the new payment history
                this.currentRent.payment_history = JSON.stringify(paymentHistory);
                
                // Save the updated rent with the new payment history
                await this.$http.post(`/hotels/rents/${this.currentRent.id}/update-payment-history`, {
                    payment_history: this.currentRent.payment_history
                });
                
                // Refresh the data
                await this.initDocument();
                
                window.location.reload();
                this.$message.success('Vuelto registrado correctamente');
                
            } catch (error) {
                console.error('Error al registrar el vuelto:', error);
                this.$message.error('Error al registrar el vuelto');
            }
        },
        getPaymentMethodDescription(paymentMethodId) {
            if (!paymentMethodId) return '-';
            const paymentTypes = this.paymentMethodTypes || [];
            const method = paymentTypes.find(m => m && (m.id === paymentMethodId || m.payment_method_type_id === paymentMethodId));
            if (method) {
                return method.description || method.payment_method_type_description || paymentMethodId;
            }
            return paymentMethodId; // Return the ID if no match found
        },
        
        getPaymentMethodClass(paymentMethodId) {
            // Default class
            let className = 'badge-light';
            
            // Get the payment method description to determine the class
            const description = this.getPaymentMethodDescription(paymentMethodId).toLowerCase();
            
            // Map payment methods to their respective classes
            if (description.includes('efectivo') || description.includes('cash')) {
                className = 'badge-success';
            } else if (description.includes('tarjeta') || description.includes('card')) {
                className = 'badge-primary';
            } else if (description.includes('transferencia') || description.includes('transfer')) {
                className = 'badge-info';
            } else if (description.includes('yape') || description.includes('plin')) {
                className = 'badge-warning';
            } else if (description.includes('depósito') || description.includes('deposit')) {
                className = 'badge-secondary';
            } else if (description.includes('crédito') || description.includes('credit')) {
                className = 'badge-dark';
            }
            
            return className;
        },
        
        getPaymentDestinationDescription(destinationId) {
            if (!destinationId) return '-';
            const destinations = this.paymentDestinations || [];
            const destination = destinations.find(d => d && d.id === destinationId);
            return (destination && destination.description) || destinationId;
        },
        
        formatDate(dateString) {
            if (!dateString) return '-';
            try {
                const date = new Date(dateString);
                return date.toLocaleString('es-ES', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            } catch (e) {
                console.error('Error formatting date:', e);
                return dateString;
            }
        },
        ...mapActions([
            'loadConfiguration',
        ]),
        validateIdentityDocumentType() {

            let identity_document_types = ["0", "1"];
            let customer = this.document.customer;

            if (
                identity_document_types.includes(customer.identity_document_type_id)
            ) {

                this.document_types = this.all_document_types.filter((row) => {
                    return ['80', '03'].includes(row.id)
                })
                this.document_types = _.sortBy(this.document_types, (row) => {
                    return row.id !== '80' ? 1 : 0;
                });

                // this.document_types = _.filter(this.all_document_types, { id: "03" });
            } else {
                this.document_types = _.sortBy(this.all_document_types, (row) => {
                    return row.id !== '80' ? 1 : 0;
                });
            }

            this.document.document_type_id =
                this.document_types.length > 0 ? this.document_types[0].id : null;
            this.changeDocumentType();
        },
        changeDateOfIssue() {
            this.document.date_of_due = this.document.date_of_issue;
        },
        changeDocumentType() {
            this.document.series_id = null;
            this.series = _.filter(this.allSeries, {
                document_type_id: this.document.document_type_id,
            });
            this.document.series_id =
                this.series.length > 0 ? this.series[0].id : null;
        },
        clickAddPayment() {

            /*
            const payment =
                this.document.payments.length == 0 ? this.document.total : 0;
            */
           console.log(this.document.payments)
            let payment = 0

            if(this.document.payments.length == 0)
            {
                if(this.totalDebt > 0)
                {
                    payment = this.totalDebt
                }
            }

            let cash = _.find(this.paymentDestinations, {id: 'cash'})
            this.document.payments.push({
                id: null,
                document_id: null,
                date_of_payment: moment().format("YYYY-MM-DD"),
                payment_method_type_id: "01",
                payment_destination_id: (cash)? cash.id : null,
                reference: null,
                payment: payment,
                display: ''
            });
        },
        onExitPage() {
            window.location.href = "/hotels/reception";
        },
        validatePaymentDestination() {
            let error_by_item = 0;

            this.document.payments.forEach((item) => {
                if (item.payment_destination_id == null) error_by_item++;
            });

            return {
                error_by_item: error_by_item,
            };
        },
        validateTotalPayments()
        {
            const total_payments = _.sumBy(this.document.payments, 'payment')

            //if(total_payments > (this.totalDebt)) return this.getResponseValidations(false, 'El total de los pagos agregados es superior al monto.')
            
            return this.getResponseValidations()
        },
        initForm() {
            this.form_cash_document = {
                document_id: null,
                sale_note_id: null,
            };
        },
        updateDataForSend() {
            console.log('updateDataForSend');

            if (this.document.document_type_id === '80') {
                this.document.prefix = 'NV'
                this.resource_documents = 'sale-notes'
            } else {
                this.document.prefix = null
                this.resource_documents = 'documents'
            }

        },
        successGoToInvoice() {
            console.log('successGoToInvoice');

            //inicializa form_cash_document
            this.initForm()

            if (this.document.document_type_id === '80') //NV
            {
                this.form_cash_document.sale_note_id = this.documentNewId
                this.showDialogSaleNoteOptions = true

            } else {
                this.form_cash_document.document_id = this.documentNewId
                this.showDialogDocumentOptions = true;
            }

        },
        async onGoToFinalizeRent() {
            this.loading = true;
            const payloadFinalizedRent = {
                arrears: this.arrears,
                selected_items: this.selectedHistoryItems
            };
            this.loading = true;
            this.$http.post( `/hotels/reception/${this.currentRent.id}/rent/finalized`,
                    payloadFinalizedRent
                )
                .then((response) => {
                    this.$message({
                        message: response.data.message,
                        type: "success",
                    });
                    window.location.href = "/hotels/reception";
                })
                .finally(() => {
                    this.loading = false
                });
        },
        async onGoToInvoice() {

            await this.onUpdateItemsWithExtras();
            await this.onCalculateTotals();
            let validate_payment_destination = this.validatePaymentDestination();

            if (validate_payment_destination.error_by_item > 0) {
                return this.$message.error("El destino del pago es obligatorio");
            }
            const validate_total_payments = this.validateTotalPayments()
            if(!validate_total_payments.success) return this.$message.error(validate_total_payments.message)

            this.updateDataForSend()
            this.loading = true;
            console.log('items: ', this.document.items);
            console.log('selectedHistoryItems: ', this.selectedHistoryItems);
            this.document.items = this.selectedHistoryItems;
            this.onCalculateTotals();
            this.document.sale_notes_relateds = this.selectedHistoryItems
                .map(item => item.sale_note_id)
                .filter(sale_note_id => sale_note_id !== null);
            this.document.sale_notes_relateds.push(...this.selectedHistoryItems.map(item => item.sale_note_ids).flat());
            this.document.sale_notes_relateds = this.document.sale_notes_relateds.filter(item => item !== null && item !== undefined);
            console.log('sale_notes_relateds: ', this.document.sale_notes_relateds);
            if(this.document.document_type_id === '80' && this.document.items.length == 1 && this.document.sale_notes_relateds.length > 0 && !this.document.items[0].extended){
                this.onGoToFinalizeRent();
                return;
            }
            this.$http
                .post(`/${this.resource_documents}`, this.document)
                .then((response) => {
                    if (response.data.success) {

                        this.documentNewId = response.data.data.id;
                        this.successGoToInvoice()

                        this.$emit("update:showDialog", false);
                        this.saveCashDocument();

                        const payloadFinalizedRent = {
                            arrears: this.arrears,
                        };
                        this.loading = true;
                        this.$http
                            .post(
                                `/hotels/reception/${this.currentRent.id}/rent/finalized`,
                                payloadFinalizedRent
                            )
                            .then((responseFinalize) => {
                                this.response = response.data;
                                this.currentRent = responseFinalize.data.currentRent
                            })
                            .finally(() => {
                                this.loading = false
                            });
                    } else {
                        this.$message.error(response.data.message);
                    }
                })
                .catch((error) => {
                    if (error.response.status === 422) {
                        this.errors = error.response.data;
                    } else {
                        this.$message.error(error.response.data.message);
                    }
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        onUpdateItemsWithExtras() {
            this.document.items = this.document.items.map((it) => {
                if (it.item_id === this.room.item_id) {
                    let dayQuantity = it.quantity;
                    const rateType = this.currentRent.rate_type === 'DAY' ? 'noche(es)' : this.currentRent.rate_type === 'HOUR' ? 'hora(s)' : 'mes(es)';
                    const name = `Habitación ${this.currentRent.room.name} x ${dayQuantity} ${rateType}`;
                    it.item.description = name;
                    it.item.full_description = name;
                    it.name_product_pdf = name;
                    it.quantity = 1;
                    const newTotal = parseFloat(it.total) + parseFloat(this.arrears);
                    console.log(newTotal);
                    it.input_unit_price_value = parseFloat(newTotal);
                    it.item.unit_price = parseFloat(newTotal);
                    it.unit_value = parseFloat(newTotal);
                    const newItem = calculateRowItem(it, "PEN", 3, this.percentage_igv);
                    return newItem;
                }
                return it;
            });
        },
        // async getItemsForSaleNote()
        // {
        //   return await this.$http.post(`/sale-notes/items-by-ids`, { ids : _.map(this.document.items, 'item_id')})
        // },
        saveCashDocument() {
            this.$http
                .post(`/cash/cash_document`, this.form_cash_document)
                .then((response) => {
                    if (!response.data.success) {
                        this.$message.error(response.data.message);
                    }
                })
                .catch((error) => {
                    this.axiosError(error);
                });
        },
        onCalculatePaidAndDebts() {
            this.totalPaid = this.currentRent.items
                .map((i) => {
                    if (i.payment_status === "PAID") {
                        return i.item.total;
                    }
                    return 0;
                })
                .reduce((a, b) => a + b, 0);
            const totalDebt = this.currentRent.items
                .map((i) => {
                    if (i.payment_status === "DEBT") {
                        return i.item.total;
                    }
                    return 0;
                })
                .reduce((a, b) => a + b, 0);
            this.totalDebt = totalDebt + parseFloat(this.arrears);
        },
        initDocument() {
            this.document = {
                customer_id: this.currentRent.customer_id,
                customer: this.currentRent.customer,
                document_type_id: null,
                series_id: null,
                prefix: null,
                establishment_id: this.config.establishment.id,
                number: "#",
                date_of_issue: moment().format("YYYY-MM-DD"),
                time_of_issue: moment().format("HH:mm:ss"),
                currency_type_id: "PEN",
                purchase_order: null,
                exchange_rate_sale: 0,
                sale_notes_relateds: [],
                total_prepayment: 0,
                total_charge: 0,
                total_discount: 0,
                total_exportation: 0,
                total_free: 0,
                total_taxed: 0,
                total_unaffected: 0,
                total_exonerated: 0,
                total_igv: 0,
                total_base_isc: 0,
                total_isc: 0,
                total_base_other_taxes: 0,
                total_other_taxes: 0,
                total_taxes: 0,
                total_value: 0,
                total: 0,
                subtotal: 0,
                operation_type_id: "0101",
                date_of_due: moment().format("YYYY-MM-DD"),
                delivery_date: moment().format("YYYY-MM-DD"),
                items: [],
                charges: [],
                discounts: [],
                attributes: [],
                guides: [],
                additional_information: null,
                actions: {
                    format_pdf: "a4",
                },
                dispatch_id: null,
                dispatch: null,
                is_receivable: false,
                payments: [],
                hotel: {},
                hotel_data_persons: this.currentRent.data_persons,
                source_module: 'HOTEL',
                hotel_rent_id: this.currentRent.id
            };
        },
        onGotoBack() {
            window.location.href = "/hotels/reception";
        },
        async savePayment(payment, index) {
            try {
                this.loading = true;
                
                // Create a new payment history array with the new payment
                const paymentHistory = this.currentRent.payment_history ? 
                    JSON.parse(this.currentRent.payment_history) : [];
                
                const newPayment = {
                    id: paymentHistory.length + 1,
                    description: 'Pago registrado',
                    type: 'advancePayment',
                    amount: parseFloat(payment.payment),
                    date: new Date().toLocaleString('es-ES'),
                    payment_method_type_id: payment.payment_method_type_id,
                    payment_destination_id: payment.payment_destination_id,
                    reference: payment.reference || ''
                };
                
                paymentHistory.push(newPayment);
                
                // Update the payment history in the rent
                await this.$http.post(`/hotels/rents/${this.currentRent.id}/update-payment-history`, {
                    payment_history: JSON.stringify(paymentHistory)
                });
                
                // Refresh the data
                await this.initDocument();
                window.location.reload();
                this.$message.success('Pago registrado correctamente');
                
            } catch (error) {
                console.error('Error saving payment:', error);
                this.$message.error('Error al registrar el pago');
            } finally {
                this.loading = false;
            }
        },
        clickCancel(index) {
            this.document.payments.splice(index, 1);
        },
        onCalculateTotals() {
            let total_exportation = 0;
            let total_taxed = 0;
            let total_exonerated = 0;
            let total_unaffected = 0;
            let total_free = 0;
            let total_igv = 0;
            let total_value = 0;
            let total = 0;
            let total_plastic_bag_taxes = 0;
            let total_discount = 0;
            let total_charge = 0;
            this.document.items.forEach((row) => {
                total_discount += parseFloat(row.total_discount);
                total_charge += parseFloat(row.total_charge);

                if (row.affectation_igv_type_id === "10") {
                    total_taxed += parseFloat(row.total_value);
                }

                if (row.affectation_igv_type_id === '20') {
                    total_exonerated += parseFloat(row.total_value)
                }

                if (["10", "20", "30", "40"].indexOf(row.affectation_igv_type_id) < 0) {
                    total_free += parseFloat(row.total_value);
                }

                if (
                    ["10", "20", "30", "40"].indexOf(row.affectation_igv_type_id) > -1
                ) {
                    total_igv += parseFloat(row.total_igv);
                    total += parseFloat(row.total);
                }

                total_value += parseFloat(row.total_value);
                total_plastic_bag_taxes += parseFloat(row.total_plastic_bag_taxes);

                if (["13", "14", "15"].includes(row.affectation_igv_type_id)) {
                    let unit_value =
                        row.total_value / row.quantity / (1 + this.percentage_igv / 100);
                    let total_value_partial = unit_value * row.quantity;
                    row.total_taxes = row.total_value - total_value_partial;
                    row.total_igv = row.total_value - total_value_partial;
                    row.total_base_igv = total_value_partial;
                    total_value -= row.total_value;
                }
            });

            this.document.total_exportation = _.round(total_exportation, 2);
            this.document.total_taxed = _.round(total_taxed, 2);
            this.document.total_exonerated = _.round(total_exonerated, 2);
            this.document.total_unaffected = _.round(total_unaffected, 2);
            this.document.total_free = _.round(total_free, 2);
            this.document.total_igv = _.round(total_igv, 2);
            this.document.total_value = _.round(total_value, 2);
            this.document.total_taxes = _.round(total_igv, 2);
            this.document.total_plastic_bag_taxes = _.round(
                total_plastic_bag_taxes,
                2
            );
            this.document.total = _.round(
                total + this.document.total_plastic_bag_taxes,
                2
            );
            this.document.subtotal = _.round(
                this.document.total,
                2
            );
        },
    },
};
</script>

<style scoped>
/* Estilos para el diálogo de resumen de pagos */
.payment-summary-dialog .el-dialog {
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
}

.payment-summary-dialog .el-dialog__header {
    background-color: #f8f9fa;
    padding: 15px 20px;
    border-bottom: 1px solid #eee;
}

.payment-summary-dialog .el-dialog__title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #2c3e50;
}

.payment-summary-dialog .el-dialog__body {
    padding: 0;
}

/* Estilos para la tabla de pagos */
.payment-row {
    transition: all 0.3s ease;
}

.payment-row:hover {
    background-color: #f8f9fa;
}

/* Estilos para los badges de tipo de pago */
.badge-pill {
    padding: 0.35em 0.8em;
    font-size: 0.85em;
    font-weight: 500;
    border-radius: 50rem;
    text-transform: capitalize;
}

/* Estilos para los íconos de estado */
.el-icon-circle-check {
    font-size: 1.1em;
}

.el-icon-refresh-left {
    font-size: 1.1em;
}

/* Estilos para los montos */
.text-success {
    color: #28a745 !important;
}

.text-danger {
    color: #dc3545 !important;
}

/* Estilos para el encabezado fijo de la tabla */
.table thead th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
    color: #6c757d;
    background-color: #f8f9fa !important;
}

/* Estilos para el mensaje de sin registros */
.text-center.py-4 {
    color: #6c757d;
    font-style: italic;
}

/* Ajustes para dispositivos móviles */
@media (max-width: 768px) {
    .payment-summary-dialog .el-dialog {
        width: 95% !important;
        margin: 10px auto;
    }
    
    .table-responsive {
        font-size: 0.9rem;
    }
    
    .badge-pill {
        padding: 0.25em 0.6em;
        font-size: 0.75em;
    }
}
</style>
