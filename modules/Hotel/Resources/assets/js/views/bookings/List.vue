<template>
  <div>
    <div class="page-header pr-0">
      <h2>
        <a href="/hotels/bookings">
          <svg xmlns="http://www.w3.org/2000/svg" style="margin-top: -5px;" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-calendar-event">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M4 5m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z" />
            <path d="M16 3l0 4" />
            <path d="M8 3l0 4" />
            <path d="M4 11l16 0" />
            <path d="M8 15h2v2h-2z" />
          </svg>
        </a>
      </h2>
      <ol class="breadcrumbs">
        <li class="active"><span>GESTIÓN DE RESERVAS</span></li>
      </ol>
      <div class="right-wrapper pull-right">
        <div class="btn-group flex-wrap">
          <button
            type="button"
            class="btn btn-custom btn-sm mt-2 mr-2"
            @click="onCreateBooking"
          >
            <i class="fa fa-plus-circle"></i> Nueva Reserva
          </button>
        </div>
      </div>
    </div>
    
    <div class="card tab-content-default row-new mb-0">
      <div class="card-body">
        <!-- Filtros -->
        <div class="row mb-4">
          <div v-if="userType==='admin'" class="col-12 col-md-3 mb-3">
            <div class="form-group">
              <label class="control-label">Establecimiento</label>
              <el-select v-model="establishment_id" @change="onFilter">
                <el-option
                  v-for="option in establishments"
                  :key="option.id"
                  :label="option.name"
                  :value="option.id"
                ></el-option>
              </el-select>
            </div>
          </div>
          
          <div class="col-12 col-md-3 mb-3">
            <div class="form-group">
              <label class="control-label">Estado</label>
              <el-select v-model="filters.status" @change="onFilter">
                <el-option label="Todas" value=""></el-option>
                <el-option label="Pendientes" value="pending"></el-option>
                <el-option label="Confirmadas" value="confirmed"></el-option>
                <el-option label="Canceladas" value="cancelled"></el-option>
                <el-option label="Finalizadas" value="completed"></el-option>
              </el-select>
            </div>
          </div>
          
          <div class="col-12 col-md-3 mb-3">
            <div class="form-group">
              <label class="control-label">Fecha de entrada</label>
              <el-date-picker
                v-model="filters.check_in"
                type="date"
                placeholder="Seleccionar fecha"
                @change="onFilter"
                style="width: 100%;"
              ></el-date-picker>
            </div>
          </div>
          
          <div class="col-12 col-md-3 mb-3 d-flex align-items-end">
            <button class="btn btn-sm btn-info" @click="resetFilters">
              <i class="fa fa-eraser"></i> Limpiar
            </button>
          </div>
        </div>
        
        <!-- Tabla de reservas -->
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>#</th>
                <th>Habitación</th>
                <th>Cliente</th>
                <th>Check-in</th>
                <th>Check-out</th>
                <th>Estado</th>
                <th>Total</th>
                <th class="text-right">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(booking, index) in bookings" :key="booking.id">
                <td>{{ index + 1 }}</td>
                <td>{{ booking.room_name || 'N/A' }}</td>
                <td>{{ booking.customer_name || 'N/A' }}</td>
                <td>{{ booking.check_in || 'N/A' }}</td>
                <td>{{ booking.check_out || 'N/A' }}</td>
                <td>
                  <span :class="getStatusClass(booking.status)">
                    {{ getStatusText(booking.status) }}
                  </span>
                </td>
                <td>{{ booking.total || '0.00' }}</td>
                <td class="text-right">
                  <button class="btn waves-effect waves-light btn-xs btn-info" @click="viewBooking(booking.id)">
                    <i class="fa fa-eye"></i>
                  </button>
                  <button class="btn waves-effect waves-light btn-xs btn-warning" @click="editBooking(booking.id)">
                    <i class="fa fa-edit"></i>
                  </button>
                  <button 
                    class="btn waves-effect waves-light btn-xs btn-danger" 
                    @click="confirmCancel(booking)"
                    :disabled="booking.status === 'cancelled' || booking.status === 'completed'"
                  >
                    <i class="fa fa-times"></i>
                  </button>
                </td>
              </tr>
              <tr v-if="bookings.length === 0">
                <td colspan="8" class="text-center">No hay reservas registradas</td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Paginación -->
        <div class="row mt-3">
          <div class="col">
            <el-pagination
              :current-page.sync="pagination.current_page"
              :page-sizes="[10, 20, 50, 100]"
              :page-size="pagination.per_page"
              layout="total, sizes, prev, pager, next"
              :total="pagination.total"
              @size-change="handleSizeChange"
              @current-change="handleCurrentChange"
            ></el-pagination>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    establishments: {
      type: Array,
      required: true,
      default: () => [],
    },
    establishmentId: {
      type: Number,
      required: true,
    },
    userType: {
      type: String,
      required: true,
    },
    company: {
      type: Object,
      required: true,
    },
  },
  
  data() {
    return {
      loading: false,
      bookings: [],
      filters: {
        status: '',
        check_in: '',
        check_out: '',
      },
      pagination: {
        current_page: 1,
        per_page: 10,
        total: 0,
      },
      establishment_id: this.establishmentId,
    };
  },
  
  mounted() {
    this.getBookings();
  },
  
  methods: {
    getBookings() {
      this.loading = true;
      // Aquí iría la llamada a la API para obtener las reservas
      // Por ahora simulamos datos de prueba
      setTimeout(() => {
        this.bookings = [
          {
            id: 1,
            room_name: 'Habitación 101',
            customer_name: 'Cliente de prueba',
            check_in: '2025-06-01',
            check_out: '2025-06-05',
            status: 'pending',
            total: '500.00',
          },
        ];
        this.pagination.total = 1;
        this.loading = false;
      }, 500);
    },
    
    onFilter() {
      this.pagination.current_page = 1;
      this.getBookings();
    },
    
    resetFilters() {
      this.filters = {
        status: '',
        check_in: '',
        check_out: '',
      };
      this.establishment_id = this.establishmentId;
      this.onFilter();
    },
    
    handleSizeChange(val) {
      this.pagination.per_page = val;
      this.getBookings();
    },
    
    handleCurrentChange(val) {
      this.pagination.current_page = val;
      this.getBookings();
    },
    
    getStatusClass(status) {
      const classes = {
        pending: 'badge badge-warning',
        confirmed: 'badge badge-success',
        cancelled: 'badge badge-danger',
        completed: 'badge badge-info',
      };
      return classes[status] || 'badge badge-secondary';
    },
    
    getStatusText(status) {
      const texts = {
        pending: 'Pendiente',
        confirmed: 'Confirmada',
        cancelled: 'Cancelada',
        completed: 'Finalizada',
      };
      return texts[status] || status;
    },
    
    onCreateBooking() {
      this.$message.info('Funcionalidad en desarrollo');
    },
    
    viewBooking(id) {
      this.$message.info(`Viendo reserva #${id}`);
    },
    
    editBooking(id) {
      this.$message.info(`Editando reserva #${id}`);
    },
    
    confirmCancel(booking) {
      this.$confirm(`¿Está seguro de cancelar la reserva #${booking.id}?`, 'Cancelar Reserva', {
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'No',
        type: 'warning',
      }).then(() => {
        this.$message.success('Reserva cancelada correctamente');
      }).catch(() => {
        this.$message.info('Operación cancelada');
      });
    },
  },
};
</script>

<style scoped>
.badge {
  padding: 5px 10px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
}

.badge-warning {
  background-color: #f8d7da;
  color: #721c24;
}

.badge-success {
  background-color: #d4edda;
  color: #155724;
}

.badge-danger {
  background-color: #f8d7da;
  color: #721c24;
}

.badge-info {
  background-color: #d1ecf1;
  color: #0c5460;
}
</style>
