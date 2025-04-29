<template>
    <main class="main">
      <Dialog
        header="Lineas"
        :visible.sync="modal1"
        :modal="true"
        :containerStyle="{width: '700px'}"
        :closable="false" :closeOnEscape="false"
      >
        <div class="toolbar-container">
          <div class="toolbar">
            <Button
              label="Nuevo"
              icon="pi pi-plus"
              class="p-button-secondary p-button-sm"
              @click="abrirModal('categoria', 'registrar')"
            />
          </div>
          <div class="search-bar">
            <span class="p-input-icon-left">
              <i class="pi pi-search" />
              <InputText
                type="text"
                placeholder="Texto a buscar"
                class="p-inputtext-sm"
                v-model="buscar"
                @keyup="buscarLinea"
              />
            </span>
          </div>
        </div>
        <DataTable
          class="p-datatable-sm p-datatable-gridlines"
          :value="arrayCategoria"
          :paginator="true"
          responsiveLayout="scroll"
          :rows="5"
        >
          <Column field="opciones" header="Opciones">
            <template #body="slotProps">
              <Button
                icon="pi pi-check"
                class="p-button-sm p-button-success custom-icon-size"
                @click="seleccionarLinea(slotProps.data)"
              />
              <Button
                icon="pi pi-pencil"
                class="p-button-warning p-button-sm"
                @click="abrirModal('categoria', 'actualizar', slotProps.data)"
              />
            </template>
          </Column>
          <Column field="id" header="Id" />
          <Column field="nombre" header="Nombre" />
          <Column field="estado" header="Estado">
            <template #body="slotProps">
                <span :class="['status-badge', slotProps.data.condicion === 1 ? 'active' : 'inactive']">
                    {{ slotProps.data.condicion === 1 ? 'Activo' : 'Inactivo' }}
                </span>
            </template>
              </Column>
        </DataTable>
        <template #footer>
          <Button
            label="Cerrar"
            icon="pi pi-times"
            class="p-button-danger p-button-sm"
            @click="closeDialog"
          />
        </template>
      </Dialog>
      <Dialog :visible.sync="modal" modal :header="tituloModal" @hide="cerrarModal"  :containerStyle="{width: '700px'}" :closable="false" :closeOnEscape="false">
          <template #footer>
            <Button label="Cancelar" icon="pi pi-times" class="p-button-sm p-button-danger" @click="cerrarModal()" />
            <Button v-if="tipoAccion === 1" label="Guardar" icon="pi pi-check" class="p-button-sm p-button-success" @click="registrarCategoria()" />
            <Button v-if="tipoAccion === 2" label="Actualizar" icon="pi pi-check" class="p-button-sm p-button-warning" @click="actualizarCategoria()" />
          </template>
          <div class="p-fluid ">
            <div class="p-field input-container">
              <label for="nombre">Nombre Línea</label>
              <InputText id="nombre" v-model="nombre" required autofocus :class="{'p-invalid': nombreError}" @input="validarNombreEnTiempoReal" />
              <small class="p-error error-message" v-if="nombreError"><strong>{{ nombreError }}</strong></small>
            </div>
         
          </div>
        </Dialog>
    </main>
  </template>

<script>
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import InputText from "primevue/inputtext";
import InputNumber from 'primevue/inputnumber';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';
export default {
    props: {
        visible: {
            type: Boolean,
            required: true,
        },
    },
    components: {
        Button,
        DataTable,
        Column,
        Dialog,
        InputText,
        InputNumber
    },
    data() {
        return {
            modal1: this.visible,
            modal: false,
            buscar: '',
            arrayCategoria:[],
            nombre: '',
            descripcion: '',
            codigoProductoSin: 0,
            codigoProductoSinError: '',
            nombreError: '',
            descripcionError: '',
            tituloModal: '',
            tipoAccion: '',
            lineaSeleccionado: null,
        };
    },
    methods: {
        closeDialog() {
            this.$emit('close');
        },
        buscarLinea() {
            this.listarCategoria(1, this.buscar,this.criterio);
        },
        seleccionarLinea(data){
            console.log("data ",data.condicion)
            if (data.condicion == 0) {
                swal({
                    title: 'Linea Inactiva',
                    text: 'No puede seleccionar esta opción porque está inactiva.',
                    type: 'warning',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Aceptar',
                    confirmButtonClass: 'btn btn-success',
                    buttonsStyling: false,
                })
            } else {
                this.lineaSeleccionado = data;
                this.$emit('linea-seleccionado', this.lineaSeleccionado);
                this.closeDialog();
            }
            
        },
        validarNombreEnTiempoReal() {
            if (!this.nombre.trim()) {
                this.nombreError = "El nombre de la linea no puede estar vacío.";
            } else {
                this.nombreError = '';
            }
        },
        validarDescripcionEnTiempoReal() {
            if (!this.descripcion.trim()) {
                this.descripcionError = "La descripción de la linea no puede estar vacía.";
            } else {
                this.descripcionError = '';
            }
        },
        validarCodigoEnTiempoReal() {
            if (this.codigoProductoSin === null || this.codigoProductoSin === undefined || String(this.codigoProductoSin).trim() === '') {
                this.codigoProductoSinError = 'El código no puede estar vacío.';
            } else {
                this.codigoProductoSinError = '';
            }
        },
        registrarCategoria() {
            if (this.validarCategoria()) {
                return;
            }
            let me = this;
            axios.post('/categoria/registrar', {
                'nombre': this.nombre,
                'descripcion': this.descripcion,
                'codigoProductoSin': this.codigoProductoSin
            }).then(function (response) {
                me.cerrarModal();
                me.listarCategoria(1, '', 'nombre');
            }).catch(function (error) {
                console.log(error);
            });
        },
        actualizarCategoria() {
            if (this.validarCategoria()) {
                return;
            }
            let me = this;
            axios.put('/categoria/actualizar', {
                'nombre': this.nombre,
                'descripcion': this.descripcion,
                'codigoProductoSin': this.codigoProductoSin,
                'id': this.categoria_id
            }).then(function (response) {
                me.cerrarModal();
                me.listarCategoria(1, '', 'nombre');
            }).catch(function (error) {
                console.log(error);
            });
        },
        validarCategoria() {
            let hasError = false;
            this.codigoProductoSinError = '';
            this.descripcionError ='';
            this.nombreError = '';
            if (!this.descripcion.trim()){
                this.descripcionError = "La descripción de la linea no puede estar vacía.";
            }
            if (this.codigoProductoSin === null || this.codigoProductoSin === undefined || String(this.codigoProductoSin).trim() === '') {
                this.codigoProductoSinError = 'El código no puede estar vacío.';
            }
            if (!this.nombre.trim()) {
                this.nombreError = "El nombre de la linea no puede estar vacío.";
            }
        },
        listarCategoria(page, buscar, criterio) {
            let me = this;
            var url = '/categorianewview?page=' + page + '&buscar=' + buscar + '&criterio=' + criterio;
            axios.get(url).then(function (response) {
                var respuesta = response.data;
                //consol.log('Linea',respuesta);
                me.arrayCategoria = respuesta.categorias;
                me.pagination = respuesta.pagination;
            })
                .catch(function (error) {
                    console.log(error);
                });
        },
        abrirModal(modelo, accion, data = []) {
            switch (modelo) {
                case "categoria":
                {
                    switch (accion) {
                        case 'registrar':
                        {
                            this.modal = true;
                            this.modal1 = false;
                            this.tituloModal = 'Registrar Categoría';
                            this.nombre = '';
                            this.descripcion = '';
                            this.codigoProductoSin = 0;
                            this.tipoAccion = 1;
                            break;
                        }
                        case 'actualizar':
                        {
                            this.modal = true;
                            this.modal1 = false;
                            this.tituloModal = 'Actualizar categoría';
                            this.tipoAccion = 2;
                            this.categoria_id = data['id'];
                            this.nombre = data['nombre'];
                            this.descripcion = data['descripcion'];
                            this.codigoProductoSin = data['codigoProductoSin'];
                            break;
                        }
                    }
                }
            }
        },
        cerrarModal() {
            this.modal = false;
            this.modal1 = true;
            this.tituloModal = '';
            this.nombre = '';
            this.descripcion = '';
            this.codigoProductoSin = '';
            this.nombreError= '';
            this.descripcionError= '';
            this.codigoProductoSinError = '';
        },
    },
    mounted(){
      this.listarCategoria(1,'','nombre');
    }
};
</script>
<style scoped>

.toolbar-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.toolbar {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 10px;
}
.search-bar {
  flex-grow: 0.5;
  display: flex;
  align-items: center;    
  justify-content: flex-start;
}
.search-bar .p-input-icon-left {
  width: 100%;
}
.search-bar .p-inputtext-sm {
  width: 100%;
}
.bold-input {
  font-weight: bold;
}
.p-fluid .p-field {
  margin-bottom: 1rem;
}
.status-badge {
  padding: 0.25em 0.5em;
  border-radius: 4px;
  color: white;
}
.status-badge.active {
  background-color: rgb(0, 225, 0);
}
.status-badge.inactive {
  background-color: red;
}
</style> 