<template>
    <div>
        <el-dialog :title="titleDialog" :visible="showDialog" @open="create"
                :close-on-click-modal="false"
                :close-on-press-escape="false"
                width="800px"
                :show-close="false">

                <div class="dialog-close-btn" style="position: absolute; top: 10px; right: 10px;">
            <el-button @click="clickClose" class="close-btn" type="text" icon="el-icon-close"></el-button>
        </div>
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 container-tabs">
                    <el-tabs v-model="activeName">
                        <el-tab-pane label="A4" name="first">
                            <iframe :src="`${form.print_a4}?cache_bust=${Date.now()}`" width="100%" height="400px"/>
                        </el-tab-pane>
                        <el-tab-pane label="Ticket 80mm" name="fourth" v-if="ShowTicket80">
                            <iframe :src="`${form.print_ticket}?cache_bust=${Date.now()}`" type="application/pdf" width="100%" height="400px"/>
                        </el-tab-pane>
                        <el-tab-pane label="Ticket 58mm" name="third" v-if="ShowTicket58">
                            <iframe :src="`${form.print_ticket_58}?cache_bust=${Date.now()}`" type="application/pdf" width="100%" height="400px"/>
                        </el-tab-pane>
                        <el-tab-pane label="Ticket 50mm" name="fifth" v-if="ShowTicket50">
                            <iframe :src="`${form.print_ticket_50}?cache_bust=${Date.now()}`" type="application/pdf" width="100%" height="400px"/>
                        </el-tab-pane>
                        <el-tab-pane label="A5" name="second">
                            <iframe :src="`${form.print_a5}?cache_bust=${Date.now()}`" type="application/pdf" width="100%" height="400px"/>
                        </el-tab-pane>
                    </el-tabs>
                    <!--<el-tabs v-model="activeName">
                        <el-tab-pane label="A4" name="first">
                            <iframe :src="form.print_a4" type="application/pdf" width="100%" height="400px"/>
                        </el-tab-pane>
                        <el-tab-pane label="Ticket 80mm" name="fourth" v-if="ShowTicket80">
                            <iframe :src="form.print_ticket" type="application/pdf" width="100%" height="400px"/>
                        </el-tab-pane>
                        <el-tab-pane label="Ticket 58mm" name="third" v-if="ShowTicket58">
                            <iframe :src="form.print_ticket_58" type="application/pdf" width="100%" height="400px"/>
                        </el-tab-pane>
                        <el-tab-pane label="Ticket 50mm" name="fifth" v-if="ShowTicket50">
                            <iframe :src="form.print_ticket_50" type="application/pdf" width="100%" height="400px"/>
                        </el-tab-pane>
                        <el-tab-pane label="A5" name="second">
                            <iframe :src="form.print_a5" type="application/pdf" width="100%" height="400px"/>
                        </el-tab-pane>
                    </el-tabs>-->

                </div>
                <div class="col-12 container-btns text-center">
                    <br><br>
                    <a :href="`https://docs.google.com/viewer?url=${form.print_a4}?format=pdf`" class="btn mx-3 btn-primary btn-lg" target="_BLANK">
                        <i class="far fa-file-pdf"></i>
                        <br>
                        <span>PDF A4</span>
                    </a>
                    <a :href="`https://docs.google.com/viewer?url=${form.print_a5}?format=pdf`" class="btn btn-primary mx-3 btn-lg" target="_BLANK">
                        <i class="far fa-file-pdf"></i>
                        <br>
                        <span>PDF A5</span>
                    </a>
                    <a :href="`https://docs.google.com/viewer?url=${form.print_ticket_58}?format=pdf`" class="btn mx-3 btn-primary btn-lg" target="_BLANK">
                        <i class="far fa-file-pdf"></i>
                        <br>
                        <span>PDF TICKET 58mm</span>
                    </a>
                    <a :href="`https://docs.google.com/viewer?url=${form.print_ticket_50}?format=pdf`" class="btn mx-3 btn-primary btn-lg" target="_BLANK">
                        <i class="far fa-file-pdf"></i>
                        <br>
                        <span>PDF TICKET 50mm</span>
                    </a>
                    <a :href="`https://docs.google.com/viewer?url=${form.print_ticket}?format=pdf`" class="btn mx-3 btn-primary btn-lg" target="_BLANK">
                        <i class="far fa-file-pdf"></i>
                        <br>
                        <span>PDF TICKET</span>
                    </a>
                </div>
            </div>
            <span slot="footer" class="dialog-footer row">
                <div class="col-md-6">
                    <el-input v-model="form.customer_email">
                        <el-button slot="append" icon="el-icon-message"   @click="clickSendEmail" :loading="loading">Enviar</el-button>
                    </el-input>
                </div>
                <div class="col-md-6"
                    v-if="!config.qr_api_enable_ws">
                    <el-input v-model="form.customer_telephone">
                        <template slot="prepend">+51</template>
                        <el-button slot="append"

                                   @click="clickSendWhatsapp">Enviar
                            <el-tooltip class="item"
                                        content="Se recomienda tener abierta la sesión de Whatsapp web"
                                        effect="dark"
                                        placement="top-start">
                                <i class="fab fa-whatsapp"></i>
                            </el-tooltip>
                        </el-button>
                    </el-input>
                    <small v-if="errors.customer_telephone"
                           class="form-control-feedback"
                           v-text="errors.customer_telephone[0]"></small>
                </div>
                <template v-else>
                    <QrApi 
                        colClass="col-md-6"
                        :wsPhone="form.customer_telephone"
                        :wsFile="form.pdf_a4_filename"
                        :wsDocument="form.number"
                        :wsMessage="form.message_text"
                        :wsData="form.pdf_a4_data"
                    />
                </template>
            <div class="col-md-6 mt-3">&nbsp;</div>
                <div class="col-md-6 mt-3">
                <template v-if="showClose">
                    <el-button @click="clickClose">Cerrar</el-button>
                </template>
                <template v-else>
                    <el-button @click="clickFinalize">Ir al listado</el-button>
                     <el-popover
                        :open-delay="1000"
                         placement="top-start"
                         width="145"
                         trigger="hover"
                         content="Presiona ALT + N">
                            <el-button slot="reference"
                                       type="primary"
                                       ref="new_note"
                                       @click="clickNewSaleNote"
                            >
                                Nueva nota de venta
                            </el-button>
                        </el-popover>
                </template>
                </div>
            </span>
        </el-dialog>

    </div>
</template>

<script>
import {mapState, mapActions} from "vuex/dist/vuex.mjs";
import QrApi from '@viewsModuleQrApi/QrApiTemplate'

export default {
    props: ['showDialog', 'recordId', 'showClose','configuration'],
    components: {
        QrApi,
    },
    data() {
        return {
            serviceUrl:"https://ej2services.syncfusion.com/production/web-services/api/pdfviewer",
            titleDialog: null,
            loading: false,
            resource: 'sale-notes',
            resource_documents: 'documents',
            errors: {},
            form: {},
            document:{},
            document_types: [],
            all_series: [],
            series: [],
            loading_submit:false,
            showDialogOptions: false,
            documentNewId: null,
            activeName: 'first',
            isSafari: false
        }
    },
    created() {
        this.initForm()
        this.loadConfiguration(this.$store)
        this.configuration = this.$store.state.config;
        console.log('this.configuration asignado:', this.configuration);
        this.$store.commit('setConfiguration', this.configuration)

    },
    mounted() {
        if(navigator.userAgent.indexOf("Safari") != -1) {
            this.isSafari = true
        }
    },
    computed: {
        ...mapState([
            'config',
        ]),
        ShowTicket58() {
            const value = this.config && this.config.show_ticket_58 !== undefined && this.config.show_ticket_58 !== null ? this.config.show_ticket_58 : false;
            console.log('ShowTicket58:', value);
            return value;
        },
        ShowTicket80() {
            const value = this.config && this.config.show_ticket_80 !== undefined && this.config.show_ticket_80 !== null ? this.config.show_ticket_80 : false;
            console.log('ShowTicket80:', value);
            return value;
        },
        ShowTicket50() {
            const value = this.config && this.config.show_ticket_50 !== undefined && this.config.show_ticket_50 !== null ? this.config.show_ticket_50 : false;
            console.log('ShowTicket50:', value);
            return value;
        }
    },
    methods: {
        ...mapActions(['loadConfiguration']),
        initForm() {
            this.errors = {}
            this.form = {
                id: null,
                external_id: null,
                identifier: null,
                date_of_issue:null,
                print_ticket: null,
                print_ticket_58: null,
                print_a4: null,
                print_a5: null,
                series:null,
                number:null,
            }
        },
        create() {
            this.$http.get(`/${this.resource}/record/${this.recordId}`)
                .then(response => {
                    this.form = response.data.data
                    this.titleDialog = `Nota de venta registrada:  ${this.form.serie}-${this.form.number}`
                })
        },
        clickFinalize() {
            location.href = `/${this.resource}`
        },
        clickNewSaleNote() {
            this.clickClose()
        },
        clickClose() {
            this.$emit('update:showDialog', false)
            this.initForm()
        },
        clickDownload(){
            window.open(`/downloads/saleNote/sale_note/${this.form.external_id}`, '_blank');
        },
        clickToPrint(format){
            window.open(`/${this.resource}/print/${this.form.id}/${format}`, '_blank');
        },
        clickSendEmail() {
            this.loading=true
            this.$http.post(`/${this.resource}/email`, {
                customer_email: this.form.customer_email,
                id: this.form.id
            })
                .then(response => {
                    if (response.data.success) {
                        this.$message.success('El correo fue enviado satisfactoriamente')
                    } else {
                        this.$message.error('Error al enviar el correo')
                    }
                })
                .catch(error => {
                    if (error.response.status === 422) {
                        this.errors = error.response.data.errors
                    } else {
                        this.$message.error(error.response.data.message)
                    }
                })
                .then(() => {
                    this.loading=false

                })
        },
        clickSendWhatsapp() {

            if (!this.form.customer_telephone) {
                return this.$message.error('El número es obligatorio')
            }

            window.open(`https://wa.me/51${this.form.customer_telephone}?text=${this.form.message_text}`, '_blank');

        },
    }
}
</script>
