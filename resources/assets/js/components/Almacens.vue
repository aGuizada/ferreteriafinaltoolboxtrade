<template>
  <div class="main">
    <Panel>
      <Toast :breakpoints="{ '920px': { width: '100%', right: '0', left: '0' } }" style="padding-top: 40px;">
                </Toast>
                <template #header>
                    <div class="panel-header">
                        <h4 class="panel-icon">Almacenes</h4>
                    </div>
                </template>
      <div class="p-grid">
        <div class="p-col-12 p-md-6">
          <div class="p-inputgroup-sm">  
            <Button icon="pi pi-plus" label="Nuevo" @click="abrirModal('almacenes', 'registrar')" />
            <Dropdown v-model="criterio" :options="criterioOptions" optionLabel="label" optionValue="value" class="p-inputgroup-addon-sm" style="width: 150px;" />
            <InputText v-model="buscar" placeholder="Texto a buscar" @keyup.enter="listarAlmacenes(1, buscar, criterio)" />
            <Button icon="pi pi-search" @click="listarAlmacenes(1, buscar, criterio)" />
          </div>
        </div>
      </div>

      <DataTable :value="arrayAlmacen" class="p-datatable-sm" :rows="10" :paginator="true" :rowsPerPageOptions="[5,10,20]" responsiveLayout="scroll">
        <Column header="Acciones">
        <template #body="slotProps">
          <Button icon="pi pi-pencil" class="p-button-warning p-button-sm" @click="abrirModal('almacenes', 'actualizar', slotProps.data)" />
          <Button icon="pi pi-trash" class="p-button-danger p-button-sm" @click="confirmarEliminar(slotProps.data)" />
        </template>
      </Column>
        <Column field="nombre_almacen" header="Nombre del Almacén"></Column>
        <Column field="ubicacion" header="Dirección (Ubicación)"></Column>
        <Column field="encargados_nombres" header="Encargado"></Column>
        <Column field="telefono" header="Teléfono"></Column>
        <Column field="nombre_sucursal" header="Sucursal"></Column>
        <Column field="observacion" header="Observación"></Column>
      </DataTable>
    </Panel>

    <Dialog :visible.sync="modal" :containerStyle="{width: '50vw'}" :modal="true" :closable="false">
      <template #header>
        <h3>{{ tituloModal }}</h3>
      </template>
      <div class="p-fluid p-grid">
                <div class="p-field p-col-12 p-md-6">
                    <label for="nombreAlmacen">Nombre del almacén *</label>
                    <InputText id="nombreAlmacen" placeholder="Ej. Almacén Principal" v-model="datosFormulario.nombre_almacen" :class="{ 'p-invalid': errores.nombre_almacen }" @input="validarCampo('nombre_almacen')" />
                    <small v-if="errores.nombre_almacen" class="p-error">{{ errores.nombre_almacen }}</small>
                </div>
                <div class="p-field p-col-12 p-md-6">
                    <label for="ubicacion">Ubicación *</label>
                    <InputText id="ubicacion" placeholder="Ej. Calle 123, Ciudad" v-model="datosFormulario.ubicacion" :class="{ 'p-invalid': errores.ubicacion }" @input="validarCampo('ubicacion')" />
                    <small v-if="errores.ubicacion" class="p-error">{{ errores.ubicacion }}</small>
                </div>
                <div class="p-field p-col-12 p-md-6">
                    <label for="encargados">Encargados *</label>
                    <AutoComplete :multiple="true" v-model="usuariosSeleccionados" :suggestions="arrayUsuario" :dropdown="true" @complete="selectUsuario($event)"  @item-select="actualizarEncargados" @item-unselect="actualizarEncargados" field="nombre" :class="{ 'p-invalid': errores.encargado }" @input="validarCampo('encargado')" placeholder="Buscar Usuarios..."/>
                    <small v-if="errores.encargado" class="p-error">{{ errores.encargado }}</small>
                </div>
                <div class="p-field p-col-12 p-md-6">
                    <label for="telefono">Teléfono *</label>
                    <InputText id="telefono" placeholder="Ej. 123456789" v-model="datosFormulario.telefono" :class="{ 'p-invalid': errores.telefono }" @input="validarCampo('telefono')" />
                    <small v-if="errores.telefono" class="p-error">{{ errores.telefono }}</small>
                </div>
                <div class="p-field p-col-12 p-md-6">
                    <label for="sucursal">Sucursal *</label>
                    <AutoComplete class="p-inputtext-sm" v-model="sucursalSeleccionado" :suggestions="arraySucursal" @complete="selectSucursal($event)" @item-select="getDatosSucursales" :dropdown="true" field="nombre" forceSelection :class="{ 'p-invalid': errores.sucursal }" placeholder="Buscar Sucursales..." />
                    <small v-if="errores.sucursal" class="p-error">{{ errores.sucursal }}</small>
                </div>
                <div class="p-field p-col-12 p-md-6">
                    <label for="observaciones">Observaciones</label>
                    <Textarea id="observaciones" placeholder="Ej. Horario de funcionamiento, Capacidad de almacenamiento, etc." rows="3" v-model="datosFormulario.observaciones" />
                </div>
            </div>

      <template #footer>
        <Button label="Cerrar" icon="pi pi-times" @click="cerrarModal" class="p-button-danger" />
        <Button v-if="tipoAccion == 1" label="Guardar" icon="pi pi-check" @click="enviarFormulario" autofocus class="p-button-success"/>
        <Button v-if="tipoAccion == 2" label="Actualizar" icon="pi pi-check" @click="enviarFormulario" autofocus class="p-button-success"/>
      </template>
    </Dialog>
  </div>
</template>

<script>
import { esquemaAlmacen } from "../constants/validations";
import Swal from 'sweetalert2';
import Panel from 'primevue/panel';
import Button from 'primevue/button';
import Dropdown from 'primevue/dropdown';
import InputText from 'primevue/inputtext';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import MultiSelect from 'primevue/multiselect';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import AutoComplete from 'primevue/autocomplete';
import Toast from 'primevue/toast';
export default {
  components: {
    Panel,
    Button,
    Dropdown,
    InputText,
    DataTable,
    Column,
    Dialog,
    MultiSelect,
    InputNumber,
    Textarea,
    AutoComplete,
    Toast,
  },
  data() {
    return {
      criterioOptions: [
        { label: 'Nombre Almacen', value: 'nombre_almacen' },
        { label: 'Nombre Encargado', value: 'nombre_encargado' },
        { label: 'Nombre Sucursal', value: 'nombre_sucursal' }
      ],
      arraySucursal: [],
      sucursalSeleccionado: null,
      arrayUsuario: [],
      usuariosSeleccionados: [],
      datosFormulario: {
        nombre_almacen: "",
        ubicacion: "",
        encargado: "",
        telefono: null,
        sucursal: null,
        observaciones: "",
      },
      errores: {},
      arrayAlmacen: [],
      modal: false,
      tituloModal: "",
      tipoAccion: 0,
      criterio: "nombre_almacen",
      buscar: "",
    };
  },
  methods: {
    confirmarEliminar(almacen) {
      Swal.fire({
        title: '¿Está seguro?',
        text: `¿Desea eliminar el almacén "${almacen.nombre_almacen}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          this.eliminarAlmacen(almacen.id);
        }
      });
    },

    eliminarAlmacen(id) {
      let me = this;
      axios.delete(`/almacen/eliminar/${id}`)
        .then(function (response) {
          Swal.fire(
            'Eliminado',
            'El almacén ha sido eliminado correctamente.',
            'success'
          );
          me.listarAlmacenes(1, "", "nombre_almacen");
        })
        .catch(function (error) {
          console.log(error);
          Swal.fire(
            'Error',
            'No se pudo eliminar el almacén',
            'error'
          );
        });
    },

    closeDialog() {
            this.$emit('close');
        },
        buscarAlmacenes(){
            this.listarAlmacenes(1,this.buscar);
        },
        selectSucursal(event) {
            let me = this;

            if (!event.query.trim().length) {

                var url = "/sucursal/selectedSucursal/filter?filtro=";
                axios.get(url).then(function (response) {
                        var respuesta = response.data;
                        me.arraySucursal = respuesta.sucursales;
                        me.loading = false;
                    })
                    .catch(function (error) {
                        console.log(error);
                        me.loading = false;
                    });
            }
            else
            {
                this.loading = true;

                var url = "/sucursal/selectedSucursal/filter?filtro=" + me.sucursalSeleccionado;
                axios.get(url).then(function (response) {
                        var respuesta = response.data;
                        me.arraySucursal = respuesta.sucursales;
                        me.loading = false;
                    })
                    .catch(function (error) {
                        console.log(error);
                        me.loading = false;
                    });
                
            }
        },
        getDatosSucursales(val1) {
            console.log("Ejecucion de sucursales");
            if (this.tipoAccion == 2) {
                this.datosFormulario.sucursal =
                val1 && val1.id ? val1.id : this.datosFormulario.sucursal2;
                delete this.datosFormulario["sucursal2"];
            } else {
                this.datosFormulario.sucursal = val1 && val1.value.id ? val1.value.id : null;
            }
        },

        actualizarEncargados() {
            const val1 = this.usuariosSeleccionados;
            if (this.tipoAccion === 2) {
                this.datosFormulario.encargado =
                    val1 && val1.length > 0
                        ? val1.map((v) => v.id).join(",")
                        : this.datosFormulario.encargado2;
                delete this.datosFormulario["encargado2"];
                console.log("form ",this.datosFormulario)

            } else {
                this.datosFormulario.encargado =
                    val1 && val1.length > 0 ? val1.map((v) => v.id).join(",") : "";
                console.log("form ",this.datosFormulario)
            }
            console.log("val 1", val1)
            
        },
        getDatosUsuarios(event) {
            const val1 = event.value;
            if (this.tipoAccion === 2) {
                this.datosFormulario.encargado =
                    val1 && val1.length > 0
                        ? val1.map((v) => v.id).join(",")
                        : this.datosFormulario.encargado2;
                delete this.datosFormulario["encargado2"];
            } else {
                this.datosFormulario.encargado =
                    val1 && val1.length > 0 ? val1.map((v) => v.id).join(",") : "";
            }
            console.log("val 1", val1)
            console.log("datos formulario", this.datosFormulario)
        },
        selectUsuario(event) {
            let me = this;

            if (!event.query.trim().length) {

                var url = "/user/selectUser/filter?idrol=3";
                axios.get(url).then(function (response) {
                        var respuesta = response.data;
                        me.arrayUsuario = respuesta.usuarios;
                        me.loading = false;
                    })
                    .catch(function (error) {
                        console.log(error);
                        me.loading = false;
                    });
            }
            else
            {
                this.loading = true;

                var url = "/user/selectUser/filter?filtro="+event.query.toLowerCase() + "&idrol=3";
                axios.get(url).then(function (response) {
                        var respuesta = response.data;
                        me.arrayUsuario = respuesta.usuarios;
                        me.loading = false;
                    })
                    .catch(function (error) {
                        console.log(error);
                        me.loading = false;
                    });
                
            }
        },
        seleccionarAlmacen(data){
            this.almacenSeleccionado = data;
            this.$emit('almacen-seleccionado', this.almacenSeleccionado);
            this.$emit('close');
        },
        async validarCampo(campo) {
            try {
                console.log("formulario",this.datosFormulario)
                await esquemaAlmacen.validateAt(campo, this.datosFormulario);
                this.errores[campo] = null;
            } catch (error) {
                this.errores[campo] = error.message;
            }
        },
        async enviarFormulario() {
  await esquemaAlmacen.validate(this.datosFormulario, { abortEarly: false })
  .then(async () => {
    // Verificar si el nombre del almacén ya existe
    let existe = await this.verificarNombreExistente();
    
    if (existe) {
      Swal.fire({
        icon: 'error',
        title: 'Nombre duplicado',
        text: 'El nombre del almacén ya existe, por favor ingrese otro nombre.',
      });
      return;
    }

    if (this.tipoAccion == 2) {
      this.actualizarAlmacen(this.datosFormulario);
    } else {
      this.registrarAlmacen(this.datosFormulario);
    }
  })
  .catch((error) => {
    console.log(error);
    const erroresValidacion = {};
    error.inner.forEach((e) => {
      erroresValidacion[e.path] = e.message;
    });
    this.errores = erroresValidacion;
  });
},
verificarNombreExistente() {
  return new Promise((resolve) => {
    let me = this;
    let url = "/almacen/verificar-nombre?nombre=" + me.datosFormulario.nombre_almacen;
    
    // Si es actualización, incluir el ID para excluirlo de la verificación
    if (me.tipoAccion == 2) {
      url += "&id=" + me.datosFormulario.id;
    }

    axios.get(url)
      .then(function(response) {
        resolve(response.data.existe);
      })
      .catch(function(error) {
        console.log(error);
        resolve(false);
      });
  });
},
        listarAlmacenes(page, buscar, criterio) {
            let me = this;
            var url =
                "/almacennewview?page=" + page + "&buscar=" + buscar + "&criterio=" + criterio;
            axios
                .get(url)
                .then(function (response) {
                let respuesta = response.data;
                me.arrayAlmacen = respuesta.almacenes;
                me.pagination = respuesta.pagination;
                console.log("Array de almacenes:", me.arrayAlmacen); // Verifica los datos en arrayAlmacen
                })
                .catch(function (error) {
                console.log(error);
                });
            },
        
            registrarAlmacen(data) {
    let me = this;
    axios.post("/almacen/registrar", data)
        .then(function(response) {
            me.cerrarModal();
            me.listarAlmacenes(1, "", "nombre_almacen");
            me.usuariosSeleccionados = [];
            me.arrayUsuario = [];
            
            // Mostrar mensaje de éxito
            Swal.fire({
                icon: 'success',
                title: 'Registro exitoso',
                text: 'El almacén ha sido registrado correctamente',
                showConfirmButton: false,
                timer: 1500
            });
        })
        .catch(function(error) {
            console.log(error);
            
            // Manejar específicamente el error de duplicado
            if (error.response && error.response.status === 422 && 
                error.response.data.message.includes('Duplicate entry')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error al registrar',
                    text: 'El nombre del almacén ya existe. Por favor ingrese otro nombre.',
                    confirmButtonText: 'Entendido'
                });
            } else {
                // Manejar otros errores
                Swal.fire({
                    icon: 'error',
                    title: 'Error al registrar',
                    text: 'Ocurrió un error al intentar registrar el almacén',
                    confirmButtonText: 'Entendido'
                });
            }
        });
},
actualizarAlmacen(data) {
    let me = this;
    axios.put("/almacen/editar", data)
        .then(function(response) {
            me.cerrarModal();
            me.listarAlmacenes(1, "", "nombre_almacen");
            
            // Mostrar mensaje de éxito
            Swal.fire({
                icon: 'success',
                title: 'Actualización exitosa',
                text: 'El almacén ha sido actualizado correctamente',
                showConfirmButton: false,
                timer: 1500
            });
        })
        .catch(function(error) {
            console.log(error);
            
            // Manejar específicamente el error de duplicado
            if (error.response && error.response.status === 422 && 
                error.response.data.message.includes('Duplicate entry')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error al actualizar',
                    text: 'El nombre del almacén ya existe. Por favor ingrese otro nombre.',
                    confirmButtonText: 'Entendido'
                });
            } else {
                // Manejar otros errores
                Swal.fire({
                    icon: 'error',
                    title: 'Error al actualizar',
                    text: 'Ocurrió un error al intentar actualizar el almacén',
                    confirmButtonText: 'Entendido'
                });
            }
        });
},
        abrirModal(modelo, accion, data = []) {
            switch (modelo) {
                case "almacenes": {
                    switch (accion) {
                        case "registrar": {
                            this.modal = true;
                            this.modal1 = false;
                            this.tituloModal = "Registrar Almacen";
                            this.tipoAccion = 1;
                            this.datosFormulario = {
                                nombre_almacen: "",
                                ubicacion: "",
                                encargado: "",
                                telefono: "",
                                sucursal: "",
                                observaciones: "",
                            };
                            this.errores = {};
                            break;
                        }
                        case "actualizar": {
                            console.log("Datos almacen:", data);
                            this.modal = true;
                            this.modal1 = false;
                            this.tituloModal = "Actualizar Almacen";
                            this.tipoAccion = 2;
                            this.datosFormulario = {
                                id: data["id"],
                                nombre_almacen: data["nombre_almacen"],
                                ubicacion: data["ubicacion"],
                                encargado: data["encargado"],
                                telefono: data["telefono"],
                                sucursal: data["sucursal"],
                                sucursal2: data["sucursal"],
                                encargado2: data["encargado"],
                                observaciones:
                                data["observacion"] == null ? "" : data["observacion"],
                            };
                            this.sucursalSeleccionado = data["nombre_sucursal"];
                            this.usuarioSeleccionado = data["nombre_encargado"];

                            this.errores = {};

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
            this.errores = {};
            this.sucursalSeleccionado = "";
            this.usuarioSeleccionado = "";
            this.usuariosSeleccionados='';
        },
    },
    mounted(){
        this.listarAlmacenes(1,'');
    }
};
</script>

<style scoped>
>>> .p-panel-header {
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

</style>
