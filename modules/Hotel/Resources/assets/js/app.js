// Hotel Module Vue Components
import Vue from 'vue';

// Hoteles :: Tarifas
Vue.component('tenant-hotel-rates', require('./views/rates/List.vue').default);

// Hoteles :: Categorías
Vue.component('tenant-hotel-categories', require('./views/categories/List.vue').default);

// Hoteles :: Pisos
Vue.component('tenant-hotel-floors', require('./views/floors/List.vue').default);

// Hoteles :: Habitaciones
Vue.component('tenant-hotel-rooms', require('./views/rooms/List.vue').default);

// Hoteles :: Recepción
Vue.component('tenant-hotel-reception', require('./views/rooms/Reception.vue').default);

// Hoteles :: Sucursales
Vue.component('tenant-hotel-sucursale', require('./views/rooms/partials/ButtonSucursales.vue').default);

// Hoteles :: Rentar habitación
Vue.component('tenant-hotel-rent', require('./views/rooms/Rent.vue').default);

// Hoteles :: Agregar producto a la habitación rentada
Vue.component('tenant-hotel-rent-add-product', require('./views/rooms/AddProductToRoom.vue').default);

// Hoteles :: Checkout
Vue.component('tenant-hotel-rent-checkout', require('./views/rooms/Checkout.vue').default);

// Hoteles :: Reservas
Vue.component('tenant-hotel-bookings', require('./views/bookings/List.vue').default);
