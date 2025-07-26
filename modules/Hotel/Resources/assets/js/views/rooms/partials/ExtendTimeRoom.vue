<template>
  <div>

    <el-dialog
      :title="title"
      :visible="visible"
      @close="closeDialog"
      width="60%"
    >
      <form autocomplete="off" @submit.prevent="onSubmit">
        <div class="form-body">
          <!-- Tarjeta de información de la habitación -->
          <div class="card mb-4" v-if="room && room.rent">
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <div class="d-flex align-items-center mb-2">
                    <i class="el-icon-date mr-2"></i>
                    <span class="font-weight-bold">Check-in:</span>
                    <span class="ml-2">
                      {{ room.rent.input_date }} a las {{ formatTime(room.rent.input_time) }}
                    </span>
                  </div>
                  
                </div>
                <div class="col-md-6">
                  <div class="d-flex align-items-center mb-2">
                    <i class="el-icon-date mr-2"></i>
                    <span class="font-weight-bold">Check-out actual:</span>
                    <span class="ml-2">
                      {{ room.rent.output_date }} a las {{ formatTime(room.rent.output_time) }}
                    </span>
                  </div>
                  <div class="d-flex align-items-center mb-2" v-if="lastNonHiddenItemPrice > 0">
                    <i class="el-icon-money mr-2"></i>
                    <span class="font-weight-bold">Precio unitario actual:</span>
                    <span class="ml-2 text-success">
                      S/ {{ lastNonHiddenItemPrice}}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="row" style="margin-bottom: -40px;">
            <div
                class="col-6 col-md-6 form-group"
                :class="{ 'has-danger': errors.custom_price }"
            >
                <label class="control-label">Tarifa</label>
                <div v-if="room_rates.length > 0" class="mb-3">
                    <el-select 
                        v-model="selected_rate" 
                        placeholder="Seleccione una tarifa"
                        style="width: 100%; margin-bottom: 15px;"
                        @change="onRateChange"
                        filterable>
                        <el-option
                            v-for="rate in room_rates"
                            :key="rate.id"
                            :label="`${rate.rate.description} - S/ ${rate.price}`"
                            :value="rate.price">
                        </el-option>
                    </el-select>
                </div>
                <div v-else-if="loadingRates" class="text-center" style="margin: 10px 0;">
                    <i class="el-icon-loading"></i> Cargando tarifas...
                </div>
                <small
                    class="form-control-feedback"
                    v-if="errors.custom_price"
                    v-text="errors.custom_price[0]"
                ></small>
            </div>
            <div
                class="col-6 col-md-6 form-group"
                :class="{ 'has-danger': errors.finalPrice }"
            >
                <label class="control-label">Precio final</label>
                <div class="mb-3">
                    <el-input 
                        v-model="finalPrice" 
                        placeholder="Precio final"
                        style="width: 100%; margin-bottom: 15px;"
                        @change="onRateChange"
                        filterable>
                    </el-input>
                </div>
                <small
                    class="form-control-feedback"
                    v-if="errors.finalPrice"
                    v-text="errors.finalPrice[0]"
                ></small>
            </div>
          </div>
          <div class="row">
            <div
                class="col-6 col-md-4 form-group"
                :class="{ 'has-danger': errors.duration }"
            >
                <label class="control-label" for="duration">Cantidad</label>
                <el-input-number
                    v-model="form.duration"
                    controls-position="right"
                    @change="updateDuration"
                ></el-input-number>
                <small class="form-control-feedback">{{ getDurationLabel() }}</small>
                <small
                    class="form-control-feedback"
                    v-if="errors.duration"
                    v-text="errors.duration[0]"
                ></small>
            </div>
            <div
                class="col-6 col-md-4 form-group"
                :class="{ 'has-danger': errors.output_date }"
            >
                <label class="control-label">Fecha de salida</label>
                <el-date-picker
                    v-model="form.output_date"
                    type="date"
                    placeholder="Seleccione una fecha"
                    value-format="yyyy-MM-dd"
                    format="yyyy-MM-dd"
                    @change="updateOutputDate"
                ></el-date-picker>
                <small
                    class="form-control-feedback"
                    v-if="errors.output_date"
                    v-text="errors.output_date[0]"
                ></small>
            </div>
            <div
                class="col-6 col-md-4 form-group"
                :class="{ 'has-danger': errors.output_time }"
            >
                <label class="control-label">Hora de salida</label>
                <el-input v-model="form.output_time"  placeholder="HH:MM">
                </el-input>
                <small
                    class="form-control-feedback"
                    v-if="errors.output_time"
                    v-text="errors.output_time[0]"
                ></small>
            </div>
            <!-- Sección de adelanto de pago -->
            <div class="col-12 col-md-12 mt-3 mb-2">
              <el-checkbox v-model="showAdvancePayment">Agregar adelanto de pago</el-checkbox>
            </div>
            
            <template v-if="showAdvancePayment">
              <div class="col-12 col-md-4 form-group">
                <label class="control-label">Método de pago</label>
                <el-select
                  v-model="form.payment_method_type_id"
                  filterable
                  placeholder="Seleccione"
                >
                  <el-option
                    v-for="option in payment_method_types"
                    :key="option.id"
                    :label="option.description"
                    :value="option.id"
                  ></el-option>
                </el-select>
              </div>
              
              <div class="col-12 col-md-4 form-group">
                <label class="control-label">Destino</label>
                <el-select
                  v-model="form.payment_destination_id"
                  filterable
                  placeholder="Seleccione"
                >
                  <el-option
                    v-for="option in payment_destinations"
                    :key="option.id"
                    :label="option.description"
                    :value="option.id"
                  ></el-option>
                </el-select>
              </div>
              
              <div class="col-12 col-md-4 form-group">
                <label class="control-label">Monto adelanto</label>
                <el-input-number
                  v-model="form.advance_amount"
                  controls-position="right"
                  :min="0"
                ></el-input-number>
              </div>
            </template>
            
            <!-- Resumen de pagos -->
            <div v-if="showAdvancePayment" class="col-12 mt-4">
              <table class="table table-bordered">
                <tbody>
                  <tr class="font-weight-bold">
                    <td colspan="3" class="text-right">Total Consumo:</td>
                    <td class="text-right">{{ calculateTotalConsumption() + (form.duration * form.custom_price || 0) | toDecimals }}</td>
                  </tr>
                  <tr class="font-weight-bold">
                    <td colspan="3" class="text-right">Total Pagado:</td>
                    <td class="text-right text-success">{{ calculateTotalPaid() + form.advance_amount | toDecimals }}</td>
                  </tr>
                  <tr v-if="(((calculateTotalConsumption() + (form.duration * form.custom_price || 0)) || 0) < calculateTotalPaid() + form.advance_amount)" class="font-weight-bold">
                    <td colspan="3" class="text-right">Vuelto:</td>
                    <td class="text-right text-info">{{ ( calculateTotalPaid() + form.advance_amount - ( calculateTotalConsumption() + (form.duration * form.custom_price || 0) ) )  | toDecimals }}</td>
                  </tr>
                  <tr v-else class="font-weight-bold">
                    <td colspan="3" class="text-right">Total a Pagar:</td>
                    <td class="text-right" :class="{ 'text-danger': calculateTotalPaid() > 0 }">
                      {{ -1 * (calculateTotalPaid() + form.advance_amount - ( calculateTotalConsumption() + (form.duration * form.custom_price || 0) ) ) | toDecimals }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            
          </div>

          <div class="row text-center ml-auto" style="width: 200px;">
            <div class="col-6">
              <el-button class="btn-block second-buton" @click="closeDialog" style="min-width: 78px;">Cancelar</el-button>
            </div>
            <div class="col-6">
              <el-button
                native-type="submit"
                type="primary"
                class="btn-block"
                :disabled="loading"
                style="min-width: 78px;"
                >Guardar</el-button
              >
            </div>            
          </div>
        </div>
      </form>
    </el-dialog>
  </div>
</template>

<script>
export default {
  props: ['room','visible'],
  computed: {
    lastNonHiddenItemPrice() {
      if (!this.room.rent.history) return 0;
      const history = this.parseJsonData(this.room.rent.history);
      // Find the last non-hidden item in reverse order
      const lastItem = [...history].reverse().find(item => !item.hidden);
      return lastItem.unit_price || 0;
    }
  },
  data() {
    return {
      form: {
        output_date: moment().format("YYYY-MM-DD"),
        output_time: moment().format("HH:mm:ss"),
        duration: 0,
        custom_price: 0,
        finalPrice:0,
        payment_method_type_id: null,
        payment_destination_id: null,
        advance_amount: 0,
        item: {}
      },
      showDialog: false,
      title: 'Editar Estadía',
      errors: {},
      loading: false,
      item: {},
      finalPrice:0,
      custom_price: 0,
      item_debt: {},
      showAdvancePayment: false,
      payment_method_types: [],
      payment_destinations: [],
      room_rates: [],
      selected_rate: null,
      loadingRates: false
    }
  },
  methods: {
    getDurationLabel() {
      switch (this.room.rent.rate_type) {
        case 'DAY':
          return 'días';
        case 'MONTH':
          return 'meses';
        case 'HOUR':
          return 'horas';
        default:
          return 'días';
      }
    },
    updateOutputDate() {
      const currentOutputDate = moment(this.room.rent.output_date, 'YYYY-MM-DD');
      const currentTime = moment(this.room.rent.output_time, 'HH:mm');
      const outputDate = moment(this.form.output_date, 'YYYY-MM-DD');
      const outputTime = moment(this.form.output_time, 'HH:mm');

      if (this.room.rent.rate_type === 'DAY') {
        this.form.duration = -1 * (currentOutputDate.diff(outputDate, 'days'));
      } else if (this.room.rent.rate_type === 'MONTH') {
        this.form.duration = -1 * (currentOutputDate.diff(outputDate, 'months'));
      } else if (this.room.rent.rate_type === 'HOUR') {
        this.form.duration = -1 * (moment(`${currentOutputDate.format('YYYY-MM-DD')} ${currentTime.format('HH:mm')}`).diff(moment(`${outputDate.format('YYYY-MM-DD')} ${outputTime.format('HH:mm')}`), 'hours'));
      }

      if(this.form.duration === 0){
        this.form.duration = 1;
      }

      this.getItem()
    },
    calculateTotalConsumption() {
      if (!this.room.rent) return 0;
      const history = this.parseJsonData(this.room.rent.history);
      if (!history.length) return 0;
      console.log(history);
                
            
      return history.reduce((total, item) => {
        return total + (parseFloat(item.total) || 0);
      }, 0);
    },
    calculateTotalPaid() {
      console.log(this.form)
      if (!this.room.rent) return 0;
      const paymentHistory = this.parseJsonData(this.room.rent.payment_history);
      if (!paymentHistory.length) return 0;
      console.log(paymentHistory);
                
            
            return paymentHistory.reduce((total, payment) => {
                return total + (parseFloat(payment.amount) || 0);
            }, 0);
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
    
    formatDate(dateString) {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toLocaleDateString('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
      });
    },
    
    formatTime(timeString) {
      if (!timeString) return '';
      const [hours, minutes] = timeString.split(':');
      return `${hours}:${minutes}`;
    },
    async fetchPaymentMethods() {
      try {
        const response = await this.$http.get('/hotels/reception/tables')
        this.payment_method_types = response.data.payment_method_types
        this.payment_destinations = response.data.payment_destinations
        
        // Set default payment method and destination if available
        if (this.payment_method_types.length > 0) {
          this.form.payment_method_type_id = this.payment_method_types[0].id
        }
        if (this.payment_destinations.length > 0) {
          this.form.payment_destination_id = this.payment_destinations[0].id
        }
      } catch (error) {
        console.error('Error fetching payment methods:', error)
      }
    },
    initForm() {
      this.form = {
        output_date: moment().format("YYYY-MM-DD"),
        output_time: moment().format("HH:mm:ss"),
        duration: 0,
        payment_method_type_id: null,
        payment_destination_id: null,
        advance_amount: 0,
        item: {}
      }
      this.item = {}
    },
    closeDialog() {
      this.initForm()
      this.$emit("onRefresh")
      this.$emit("update:visible", false)
    },
    async create() {
      console.log(this.room)
      this.form = {
        output_date: moment(this.room.rent.output_date).subtract(1, 'days').format("YYYY-MM-DD"),
        output_time: moment(this.room.rent.output_time).format("HH:mm:ss"),
        duration: 0,
        payment_method_type_id: null,
        payment_destination_id: null,
        advance_amount: 0
      }
      await this.fetchPaymentMethods()
      await this.fetchRoomRates()
      this.updateDuration()
    },
    
    async fetchRoomRates() {
      this.loadingRates = true;
      this.room_rates = [];
      this.selected_rate = null;
      
      try {
        const response = await this.$http.get(`/hotels/rooms/${this.room.id}/rates`);
        this.room_rates = response.data.room_rates || [];
        
        // Set initial custom price to the first rate if available
        if (this.room_rates.length > 0) {
          this.selected_rate = this.room_rates[0].price;
          this.form.custom_price = this.room_rates[0].price;
          this.finalPrice = this.room_rates[0].price;
          this.updateDuration();
        }
      } catch (error) {
        console.error('Error fetching room rates:', error);
        this.$message.error('No se pudieron cargar las tarifas de la habitación');
      } finally {
        this.loadingRates = false;
      }
    },
    
    onRateChange(price) {
      this.form.custom_price = price;
      this.finalPrice = price;
      this.updateDuration();
    },
    updateDuration() {
      const currentOutputDate = moment(this.room.rent.output_date, 'YYYY-MM-DD');
      const currentTime = moment(this.room.rent.output_time, 'HH:mm');
      
      let newDate;
      if (this.room.rent.rate_type === 'DAY') {
        newDate = currentOutputDate.add(this.form.duration, 'days');
      } else if (this.room.rent.rate_type === 'MONTH') {
        newDate = currentOutputDate.add(this.form.duration, 'months');
      } else if (this.room.rent.rate_type === 'HOUR') {
        newDate = moment(`${currentOutputDate.format('YYYY-MM-DD')} ${currentTime.format('HH:mm')}`).add(this.form.duration, 'hours');
      }
      
      this.form.output_date = newDate.format("YYYY-MM-DD");
      if(this.room.rent.rate_type === 'HOUR'){
        this.form.output_time = newDate.format("HH:mm");
      }else{
        this.form.output_time = '12:00';
      }

      this.getItem()
    },
    getItem() {
      
      this.$http
          .get(`/hotels/reception/${this.room.rent.id}/rent/get-item`)
          .then(response => {
            this.item = response.data.data.item
            this.item_debt = response.data.data.item_debt
            if(this.item_debt && this.item_debt.payment_status=='DEBT'){
              this.changeJsonItem()
            }else{
              this.addJsonItem()
            }
            
            
            
          })
    },
    changeJsonItem() {

      let quantity = this.form.duration + this.room.rent.duration

      if(this.item && this.item.payment_status=='PAID'){
        quantity = quantity - this.item.item.quantity;
        console.log(this.item.item.quantity)
      }
      
      console.log(quantity)
      let variation = (this.finalPrice - this.item_debt.item.unit_price ) * this.form.duration

      console.log(this.item_debt)
      
      let percentage_igv = this.item_debt.item.percentage_igv
      let unit_price = this.item_debt.item.unit_price
      let total = this.item_debt.item.total + (this.form.duration * unit_price) + variation
      let total_base_igv = total / (1 + (percentage_igv / 100))
      let total_igv = total - total_base_igv

      this.item_debt.item.quantity = quantity
      this.item_debt.item.unit_value = _.round(unit_price, 2)
      this.item_debt.item.input_unit_price_value = _.round(unit_price, 2)
      this.item_debt.item.total = _.round(total, 2)
      this.item_debt.item.total_base_igv = _.round(total_base_igv, 2)
      this.item_debt.item.total_value = _.round(total_base_igv, 2)
      this.item_debt.item.total_taxes = _.round(total_igv, 2)
      this.item_debt.item.total_igv = _.round(total_igv, 2)

      this.item_debt.item.total_value_without_rounding = total_base_igv
      this.item_debt.item.total_base_igv_without_rounding = total_base_igv
      this.item_debt.item.total_igv_without_rounding = total_igv
      this.item_debt.item.total_taxes_without_rounding = total_igv
      this.item_debt.item.total_without_rounding = total

      this.form.item = this.item_debt
      console.log(this.form.item)
      this.$forceUpdate()
    },
    addJsonItem() {

      let quantity = this.form.duration + this.room.rent.duration

      if(this.item && this.item.payment_status=='PAID'){
        quantity = quantity - this.item.item.quantity;
        console.log(this.item.item.quantity)
      }

      let variation = (this.finalPrice - this.item.item.unit_price ) * this.form.duration

      let percentage_igv = this.item.item.percentage_igv
      let unit_price = this.item.item.unit_price
      let total = (quantity * unit_price) + variation
      let total_base_igv = total / (1 + (percentage_igv / 100))
      let total_igv = total - total_base_igv

      this.item.item.quantity = quantity
      this.item.item.unit_value = _.round(unit_price, 2)
      this.item.item.input_unit_price_value = _.round(unit_price, 2)
      this.item.item.total = _.round(total, 2)
      this.item.item.total_base_igv = _.round(total_base_igv, 2)
      this.item.item.total_value = _.round(total_base_igv, 2)
      this.item.item.total_taxes = _.round(total_igv, 2)
      this.item.item.total_igv = _.round(total_igv, 2)

      this.item.item.total_value_without_rounding = total_base_igv
      this.item.item.total_base_igv_without_rounding = total_base_igv
      this.item.item.total_igv_without_rounding = total_igv
      this.item.item.total_taxes_without_rounding = total_igv
      this.item.item.total_without_rounding = total
      this.form.item = this.item
      this.$forceUpdate()
    },
    onSubmit() {
      this.loading = true
      console.log(this.form);
      this.form.duration = this.form.duration + this.room.rent.duration
      this.form.output_date = moment(this.form.output_date).format("YYYY-MM-DD")
      this.$http
        .post(`/hotels/reception/${this.room.rent.id}/rent/extend-time`, this.form)
        .then((response) => {
          this.$message.success(response.data.message);
          this.closeDialog()
        })
        .catch((error) => this.axiosError(error))
        .finally(() => (this.loading = false));
    }
  },
  watch: {
    visible: {
      immediate: true,
      async handler(newVal) {
        if (newVal) {
          await this.create();
        }
      }
    }
  },
  mounted() {
    this.fetchPaymentMethods();
  }
}
</script>