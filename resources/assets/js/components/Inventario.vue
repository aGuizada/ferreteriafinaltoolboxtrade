<template>
  <div class="main">
    <Panel>
      <Toast :breakpoints="{ '920px': { width: '100%', right: '0', left: '0' } }" style="padding-top: 40px;"></Toast>
      
      <template #header>
        <div class="panel-header">
          <h4 class="panel-icon">Inventarios</h4>
        </div>
      </template>

      <div class="p-grid p-ai-center p-mb-2">
        <!-- Botón Importar -->
        <div class="p-col-12 p-md-3 p-lg-2 p-mb-2">
          <Button label="Importar" icon="pi pi-plus" @click="abrirModalImportar" class="p-button-success w-full" />
        </div>

        <!-- Buscador -->
        <div class="p-col-12 p-md-5 p-lg-4 p-mb-2">
          <span class="p-input-icon-left p-input-icon-right w-full">
            <i class="pi pi-search" />
            <InputText 
              v-model="buscar" 
              placeholder="Buscar por nombre de producto"
              @input="listarInventario(1, buscar, criterio)" 
              class="w-full" 
            />
          </span>
        </div>

        <!-- Filtro Almacén -->
        <div class="p-col-12 p-md-4 p-lg-2 p-mb-2">
          <label class="p-text-bold">ALMACÉN</label>
          <Dropdown 
            v-model="almacenSeleccionado" 
            :options="arrayAlmacenes"
            optionLabel="nombre_almacen" 
            optionValue="id" 
            placeholder="Seleccione"
            @change="getDatosAlmacen" 
            class="w-full" 
          />
        </div>

        <!-- RadioButtons Por Item / Por Lote -->
        <div class="p-col-12 p-md-4 p-lg-2 p-mb-2">
          <div class="p-field-radiobutton">
            <RadioButton id="tipo1" name="tipo" value="item" v-model="tipoSeleccionado" @change="cambiarTipo" />
            <label for="tipo1" class="p-ml-2">Por Item</label>
          </div>
          <div class="p-field-radiobutton">
            <RadioButton id="tipo2" name="tipo" value="lote" v-model="tipoSeleccionado" @change="cambiarTipo" />
            <label for="tipo2" class="p-ml-2">Por Lote</label>
          </div>
        </div>

        <!-- Botón Ver Resumen -->
        <div class="p-col-12 p-md-3 p-lg-2 p-mb-2">
          <Button 
            label="Ver Resumen de Inversión" 
            @click="abrirModalResumen"
            class="p-button-info w-full" 
          />
        </div>
      </div>

      <DataTable :value="arrayInventario" :rows="10" :rowsPerPageOptions="[5, 10, 20]"
        responsiveLayout="scroll" class="p-datatable-gridlines p-datatable-sm moto-table">
        <Column v-for="col in columnas" :key="col.field" :field="col.field" :header="col.header" 
          :body="col.body || null" :escape="false"></Column>
      </DataTable>

      <!-- Modal de Resumen -->
      <div class="modal fade" id="modalResumen" tabindex="-1" aria-labelledby="modalResumenLabel" aria-hidden="true" ref="modalResumen">
        <div class="modal-dialog modal-dialog-centered modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="modalResumenLabel">Resumen de Inversión en Inventario</h5>
              <button type="button" class="btn-close" @click="cerrarModalResumen" aria-label="Close"></button>
            </div>

            <div class="modal-body">
              <div class="row">
                <div class="col-12 col-md-6">
                  <h4>Total Inversión:</h4>
                  <p class="fw-bold fs-4">
                    ${{ totalInversion.toFixed(2) }}
                  </p>
                </div>
                <div class="col-12 col-md-6">
                  <h4>Productos con Stock:</h4>
                  <p class="fw-bold fs-4">
                    {{ totalProductosConStock }}
                  </p>
                </div>
              </div>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-success" @click="generarReporte">
                <i class="bi bi-download"></i> Descargar Reporte
              </button>
              <button type="button" class="btn btn-danger" @click="cerrarModalResumen">
                <i class="bi bi-x"></i> Cerrar
              </button>
            </div>
          </div>
        </div>
      </div>

      <Paginator :rows="10" :totalRecords="pagination.total" @page="onPageChange"
        :rowsPerPageOptions="[5, 10, 20]" />
    </Panel>

    <div v-if="modalImportar">
      <ImportarExcelInventario @cerrar="cerrarModalImportar" />
    </div>
  </div>
</template>

<script>
import Card from 'primevue/card';
import Button from 'primevue/button';
import Dropdown from 'primevue/dropdown';
import RadioButton from 'primevue/radiobutton';
import InputText from 'primevue/inputtext';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Paginator from 'primevue/paginator';
import Dialog from 'primevue/dialog';
import Panel from 'primevue/panel';
import Toast from 'primevue/toast';
import axios from 'axios';

export default {
  components: {
    Card,
    Button,
    Dropdown,
    RadioButton,
    InputText,
    DataTable,
    Column,
    Paginator,
    Dialog,
    Panel,
    Toast,
  },
  data() {
    return {
      arrayInventario: [],
      arrayAlmacenes: [],
      almacenSeleccionado: 1,
      idalmacen: 0,
      tipoSeleccionado: 'lote',
      modalImportar: false,
      pagination: {
        total: 0,
        current_page: 0,
        per_page: 0,
        last_page: 0,
        from: 0,
        to: 0,
      },
      criterio: 'nombre',
      buscar: '',
      columnas: [],
      totalInversion: 0,
      totalProductosConStock: 0,
    };
  },
  mounted() {
    this.selectAlmacen();
    this.listarInventario(1, '', this.criterio);
    this.setColumnas();
  },
  methods: {
    abrirModalImportar() {
      this.modalImportar = true;
    },
    cerrarModalImportar() {
      this.modalImportar = false;
      this.listarInventario(1, '', this.criterio);
    },
    abrirModalResumen() {
      $('#modalResumen').modal('show');
      this.calcularResumen();
    },
    cerrarModalResumen() {
      $('#modalResumen').modal('hide');
    },
    cambiarTipo() {
      this.listarInventario(1, '', this.criterio);
      this.setColumnas();
    },
    listarInventario(page, buscar, criterio) {
      const url = `/inventarios/itemLote/${this.tipoSeleccionado}?idAlmacen=${this.almacenSeleccionado}&buscar=${buscar}&criterio=${criterio}&page=${page}&incluir_vencimiento=true`;
      axios.get(url).then(response => {
        this.arrayInventario = response.data.inventarios.data;
        this.pagination = response.data.pagination;
      }).catch(error => {
        console.error(error);
      });
    },
    selectAlmacen() {
      axios.get('/almacen/selectAlmacen').then(response => {
        this.arrayAlmacenes = response.data.almacenes;
      }).catch(error => {
        console.error(error);
      });
    },
    getDatosAlmacen() {
      if (this.almacenSeleccionado) {
        this.idalmacen = Number(this.almacenSeleccionado);
        this.listarInventario(1, '', this.criterio);
      }
    },
    onPageChange(event) {
      this.listarInventario(event.page + 1, this.buscar, this.criterio);
    },
    calcularResumen() {
      if (this.tipoSeleccionado === 'item') {
        const productosConStock = this.arrayInventario.filter(item => item.saldo_stock_total > 0);
        this.totalInversion = productosConStock.reduce((acc, item) => acc + (item.saldo_stock_total * item.precio_costo_unid), 0);
        this.totalProductosConStock = productosConStock.length;
      } else {
        const lotesConStock = this.arrayInventario.filter(item => item.saldo_stock > 0);
        this.totalInversion = lotesConStock.reduce((acc, item) => acc + (item.saldo_stock * item.precio_costo_unid), 0);
        this.totalProductosConStock = new Set(lotesConStock.map(item => item.nombre_producto)).size;
      }
    },
    generarReporte() {
      axios({
        url: '/inventarios/generar-reporte-inversion',
        method: 'GET',
        responseType: 'blob',
        params: {
          idAlmacen: this.almacenSeleccionado,
          tipo: this.tipoSeleccionado
        }
      }).then(response => {
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', 'reporte_inversion.pdf');
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      }).catch(error => {
        console.error('Error al generar el reporte:', error);
        this.$toast.add({
          severity: 'error',
          summary: 'Error',
          detail: 'Ocurrió un error al generar el reporte',
          life: 3000
        });
      });
    },
    formatearFecha(fecha) {
      if (!fecha || fecha === '0000-00-00' || fecha === '2099-12-31') {
        return 'Sin vencimiento';
      }
      
      const fechaVencimiento = new Date(fecha);
      const hoy = new Date();
      const diffTime = fechaVencimiento - hoy;
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
      
      const options = { year: 'numeric', month: '2-digit', day: '2-digit' };
      const fechaFormateada = fechaVencimiento.toLocaleDateString('es-ES', options);
      
      if (diffDays < 0) {
        return `<span class="fecha-vencida">${fechaFormateada} (Vencido)</span>`;
      } else if (diffDays <= 30) {
        return `<span class="fecha-proxima">${fechaFormateada} (Próximo)</span>`;
      }
      
      return fechaFormateada;
    },
    setColumnas() {
      if (this.tipoSeleccionado === 'item') {
        this.columnas = [
          { field: 'nombre_producto', header: 'Producto' },
          { field: 'unidad_envase', header: 'Unidad X Paq.' },
          { field: 'saldo_stock_total', header: 'Saldo Stock Total' },
          { field: 'cantidad', header: 'Cantidad' },
    
          { field: 'precio_costo_unid', header: 'Precio Unitario', body: data => `$${parseFloat(data.precio_costo_unid).toFixed(2)}` },
          { field: 'inversion_total', header: 'Inversión', body: data => `$${parseFloat(data.inversion_total).toFixed(2)}` },
          { field: 'nombre_almacen', header: 'Almacén' },
        ];
      } else {
        this.columnas = [
          { field: 'nombre_producto', header: 'Producto' },
          { field: 'cantidad', header: 'Cantidad' },
          { field: 'saldo_stock', header: 'Saldo Stock' },
          { field: 'unidad_envase', header: 'Unidad X Paq.' },
          { 
            field: 'fecha_vencimiento', 
            header: 'Fecha Vencimiento',
            body: data => this.formatearFecha(data.fecha_vencimiento)
          },
          { field: 'precio_costo_unid', header: 'Precio Unitario', body: data => `$${parseFloat(data.precio_costo_unid).toFixed(2)}` },
          { field: 'nombre_almacen', header: 'Almacén' },
        ];
      }
    }
  }
};
</script>

<style scoped>
>>>.p-panel-header {
  padding: 0.75rem;
}

.panel-header {
  display: flex;
  align-items: center;
}

.panel-icon {
  font-size: 2rem;
  padding-left: 10px;
}

.panel-icon {
  font-size: 1.5rem;
  margin: 0;
}

.p-field-radiobutton {
  display: inline-block;
  margin-right: 1rem;
}

.w-full {
  width: 100%;
}

.total-inversion {
  font-size: 1.5rem;
  font-weight: bold;
  color: #2c3e50;
}

.total-productos {
  font-size: 1.5rem;
  font-weight: bold;
  color: #27ae60;
}

.moto-table {
  font-size: 0.9rem;
}

.moto-table th {
  background-color: #f8f9fa;
  font-weight: 600;
}

>>> .p-datatable .p-datatable-tbody > tr > td {
  padding: 0.5rem 1rem;
}

.fecha-vencida {
  color: #ff0000;
  font-weight: bold;
}

.fecha-proxima {
  color: #ffa500;
  font-weight: bold;
}
</style>