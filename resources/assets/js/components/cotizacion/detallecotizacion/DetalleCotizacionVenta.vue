<template>
    <Dialog :visible="true" :containerStyle="{width: '700px'}" :modal="true" :closable="false" header="Cotizacion Venta">
      <div class="p-d-flex p-jc-between p-ai-center p-mb-4">
        <Button label="Realizar Venta" icon="pi pi-shopping-cart" class="p-button-success" @click="abrirVenta" />
      </div>
      
      <div class="p-grid">
        <div class="p-col-3"><strong>ID:</strong></div>
        <div class="p-col-9">{{ arrayCotizacionSeleccionado.id }}</div>
        
        <div class="p-col-3"><strong>Fecha Venta:</strong></div>
        <div class="p-col-9">{{ arrayCotizacionSeleccionado.fecha_hora }}</div>
        
        <div class="p-col-3"><strong>Cliente:</strong></div>
        <div class="p-col-9">{{ arrayCotizacionSeleccionado.nombre }}</div>
        
        <div class="p-col-3"><strong>NIT/CI:</strong></div>
        <div class="p-col-9">{{ arrayCotizacionSeleccionado.fecha_hora }}</div>
        
        <div class="p-col-3"><strong>IMPUESTO:</strong></div>
        <div class="p-col-9">{{ arrayCotizacionSeleccionado.impuesto }}</div>
      </div>
      
      <DataTable :value="arrayCotizacionVentDet" class="p-mt-4">
        <Column field="codigo" header="CODIGO"></Column>
        <Column field="nombre_articulo" header="PRODUCTO"></Column>
        <Column field="cantidad" header="CANTIDAD"></Column>
        <Column field="descuento" header="DESCUENTO"></Column>
        <Column field="prectotal" header="IMPORTE"></Column>
      </DataTable>
      
      <template #footer>
        <Button label="Cerrar" icon="pi pi-times" class="p-button-text" @click="cerrarModal" />
      </template>
    </Dialog>
  </template>
  
  <script>
  import Dialog from 'primevue/dialog';
  import Button from 'primevue/button';
  import DataTable from 'primevue/datatable';
  import Column from 'primevue/column';
  
  export default {
    components: {
      Dialog,
      Button,
      DataTable,
      Column
    },
    props: {
      arrayCotizacionVentDet: {
        type: Array,
        required: true
      },
      arrayCotizacionSeleccionado: {
        type: Object,
        required: true
      }
    },
    methods: {
      abrirVenta() {
        const datos = {
          cotizacion: this.arrayCotizacionSeleccionado,
          detalles: this.arrayCotizacionVentDet
        }
        this.$emit('abrirVenta', datos);
      },
      cerrarModal() {
        this.$emit('cerrar');
      }
    }
  };
  </script>