<template>
    <div class="main">
      <Panel>
        <Toast :breakpoints="{ '920px': { width: '100%', right: '0', left: '0' } }" style="padding-top: 40px;">
                </Toast>
                <template #header>
                    <div class="panel-header">
                        <h4 class="panel-icon">Usuarios</h4>
         
                      </div>
                </template>
      
        <template>
          
          <span>
              <Button icon="pi pi-plus" class="p-button-secondary" @click="abrirModal('persona', 'registrar')" label="Nuevo" />
              <Button icon="pi pi-file-excel" class="p-button-info" @click="cargarReporteUsuariosExcel()" label="Reporte" />
            </span>
          <div class="p-fluid p-formgrid p-grid">
            <div class="p-field-sm">
              <Dropdown v-model="criterio"  :options="criterioOptions" optionLabel="label" optionValue="value" placeholder="Seleccione criterio" />
            </div>
            <div class="p-field ">
              <span class="p-input-icon-left">
                <i class="pi pi-search" />
                <InputText v-model="buscar" placeholder="Buscar..." @input="listarPersona(1, buscar, criterio)" />
              </span>
            </div>
          </div>
  
         <DataTable :value="arrayPersona" :paginator="true" :rows="10"
                     :totalRecords="pagination.total" :lazy="true"
                     @page="onPage($event)" :loading="loading"
                     dataKey="id" class="p-datatable-gridlines p-datatable-sm">
            <Column>
              <template #body="slotProps">
                <Button icon="pi pi-pencil" class="p-button p-button-warning p-mr-2" @click="abrirModal('persona', 'actualizar', slotProps.data)" />
                <Button v-if="slotProps.data.condicion" icon="pi pi-trash" class="p-button p-button-danger" @click="desactivarUsuario(slotProps.data.id)" />
                <Button v-else icon="pi pi-check" class="p-button p-button-info" @click="activarUsuario(slotProps.data.id)" />
              </template>
            </Column>
            <Column header="Foto">
              <template #body="slotProps">
                <img :src="'img/usuarios/' + (slotProps.data.fotografia || 'defecto.jpg') + '?t=' + new Date().getTime()" width="50" height="50" />
              </template>
            </Column>
            <Column field="nombre" header="Nombre"></Column>
            <Column field="tipo_documento" header="Tipo Documento"></Column>
            <Column field="num_documento" header="Número"></Column>
            <Column field="direccion" header="Dirección"></Column>
            <Column field="telefono" header="Teléfono"></Column>
            <Column field="email" header="Email"></Column>
            <Column field="usuario" header="Usuario"></Column>
            <Column field="rol" header="Rol"></Column>
            <Column field="sucursal" header="Sucursal"></Column>
          </DataTable>
        </template>
      </Panel>
  
      <Dialog :visible.sync="modal" :containerStyle="{width: '550px'}" :modal="true" style="padding-top: 35px !important;">
        <template #header>
          <h3>{{ tituloModal }}</h3>
        </template>
        
        <div class="p-fluid p-formgrid p-grid">
          <div class="p-field p-col-12 p-md-6">
            <label for="nombre">Nombre(*)</label>
            <InputText class="p-inputtext-sm" id="nombre" v-model="nombre" />
          </div>
          <div class="p-field p-col-12 p-md-6">
            <label for="tipo_documento">Tipo documento</label>
            <Dropdown class="p-inputtext-sm" id="tipo_documento" v-model="tipo_documento" :options="tipoDocumentoOptions" optionLabel="label" optionValue="value" placeholder="Selecciona un tipo de documento" />
          </div>
          <div class="p-field p-col-12 p-md-6">
            <label for="num_documento">Número documento</label>
            <InputText class="p-inputtext-sm" id="num_documento" v-model="num_documento" />
          </div>
          <div class="p-field p-col-12 p-md-6">
            <label for="direccion">Dirección</label>
            <InputText class="p-inputtext-sm" id="direccion" v-model="direccion" />
          </div>
          <div class="p-field p-col-12 p-md-6">
            <label for="telefono">Teléfono</label>
            <InputText class="p-inputtext-sm" id="telefono" v-model="telefono" />
          </div>
          <div class="p-field p-col-12 p-md-6">
            <label for="email">Email</label>
            <InputText class="p-inputtext-sm"  id="email" v-model="email" />
          </div>
          <div class="p-field p-col-12 p-md-6">
            <label for="idrol">Rol</label>
            <Dropdown class="p-inputtext-sm" id="idrol" v-model="idrol" :options="arrayRol" optionLabel="nombre" optionValue="id" placeholder="Seleccione" />
          </div>
          <div class="p-field p-col-12 p-md-6">
            <label for="idsucursal">Sucursal</label>
            <Dropdown class="p-inputtext-sm" id="idsucursal" v-model="idsucursal" :options="arraySucursal" optionLabel="nombre" optionValue="id" placeholder="Seleccione" />
          </div>
          <div class="p-field p-col-12 p-md-6">
            <label for="usuario">Usuario</label>
            <InputText class="p-inputtext-sm" id="usuario" v-model="usuario" />
          </div>
          <div class="p-field p-col-12 p-md-6">
            <label for="password">Clave</label>
            <Password class="p-inputtext-sm" id="password" v-model="password" />
          </div>
          <div class="p-field p-col-12">
            <label for="fotografia">Fotografía</label>
            <FileUpload class="p-inputtext-sm" mode="basic" name="fotografia" url="./upload" accept="image/*" :maxFileSize="1000000" @upload="onUpload" />
          </div>
        </div>
  
        <template #footer>
          <Button label="Cerrar" icon="pi pi-times" @click="cerrarModal" class="p-button-text"/>
          <Button v-if="tipoAccion == 1" label="Guardar" icon="pi pi-check" @click="registrarPersona" />
          <Button v-if="tipoAccion == 2" label="Actualizar" icon="pi pi-check" @click="actualizarPersona" />
        </template>
      </Dialog>
    </div>
  </template>
  
  <script>
  import Button from 'primevue/button';
import Card from 'primevue/card';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import FileUpload from 'primevue/fileupload';
import InputText from 'primevue/inputtext';
import Panel from 'primevue/panel';
import Password from 'primevue/password';
import Toast from 'primevue/toast';
  export default {
    components: {
      Card,
      Button,
      Dropdown,
      InputText,
      DataTable,
      Column,
      Dialog,
      Password,
      FileUpload,
      Panel,
        Toast,
    },
    data() {
      return {
        tipoDocumentoOptions: [
        {label: 'CI - CEDULA DE IDENTIDAD', value: '1'},
        {label: 'CEX - CEDULA DE IDENTIDAD DE EXTRANJERO', value: '2'},
        {label: 'NIT - NÚMERO DE IDENTIFICACIÓN TRIBUTARIA', value: '5'},
        {label: 'PAS - PASAPORTE', value: '3'},
        {label: 'OD - OTRO DOCUMENTO DE IDENTIDAD', value: '4'}
      ],
        criterioOptions: [
          {label: 'Nombre', value: 'nombre'},
          {label: 'Documento', value: 'num_documento'},
          {label: 'Email', value: 'email'},
          {label: 'Teléfono', value: 'telefono'},
          {label: 'Sucursal', value: 'nombre'}
        ],
        loading: false,
        persona_id: 0,
        nombre: '',
        tipo_documento: '',
        num_documento: '',
        direccion: '',
        telefono: '',
        email: '',
        usuario: '',
        password: '',
        fotografia: '',
        fotoMuestra: '',
        idrol: '',
        idsucursal: '',
        arrayPersona: [],
        arrayRol: [],
        arraySucursal: [],
        modal: false,
        tituloModal: '',
        tipoAccion: 0,
        errorPersona: 0,
        errorMostrarMsjPersona: [],
        pagination: {
          'total': 0,
          'current_page': 0,
          'per_page': 0,
          'last_page': 0,
          'from': 0,
          'to': 0,
        },
        offset: 3,
        criterio: 'nombre',
        buscar: ''
      }
    },
    computed: {
      isActived: function () {
        return this.pagination.current_page;
      },
      pagesNumber: function () {
        if (!this.pagination.to) {
          return [];
        }
  
        var from = this.pagination.current_page - this.offset;
        if (from < 1) {
          from = 1;
        }
  
        var to = from + (this.offset * 2);
        if (to >= this.pagination.last_page) {
          to = this.pagination.last_page;
        }
  
        var pagesArray = [];
        while (from <= to) {
          pagesArray.push(from);
          from++;
        }
        return pagesArray;
      },
      imagen() {
        console.log(this.fotoMuestra);
        return this.fotoMuestra;
      }
    },
    methods: {
        onUpload(event) {
      // Manejar la carga de archivos
      this.fotografia = event.files[0];
    },
    onPage(event) {
      this.listarPersona(event.page + 1, this.buscar, this.criterio);
    },
      listarPersona(page, buscar, criterio) {
        let me = this;
        var url = '/user?page=' + page + '&buscar=' + buscar + '&criterio=' + criterio;
        axios.get(url).then(function (response) {
          var respuesta = response.data;
          me.arrayPersona = respuesta.personas.data;
          me.pagination = respuesta.pagination;
        })
          .catch(function (error) {
            console.log(error);
          });
      },
      selectRol() {
        let me = this;
        var url = '/rol/selectRol';
        axios.get(url).then(function (response) {
          var respuesta = response.data;
          me.arrayRol = respuesta.roles;
        })
          .catch(function (error) {
            console.log(error);
          });
      },
      selectSucursal() {
        let me = this;
        var url = '/sucursal/selectSucursal';
        axios.get(url).then(function (response) {
          var respuesta = response.data;
          me.arraySucursal = respuesta.sucursales;
        })
          .catch(function (error) {
            console.log(error);
          });
      },
      cambiarPagina(page, buscar, criterio) {
        let me = this;
        me.pagination.current_page = page;
        me.listarPersona(page, buscar, criterio);
      },
      obtenerFotografia(event) {
        let file = event.target.files[0];
        let fileType = file.type;
        if (fileType !== 'image/png' && fileType !== 'image/jpeg') {
          alert('Por favor, seleccione una imagen en formato PNG o JPG.');
          return;
        }
        this.fotografia = file;
        this.mostrarFoto(file);
      },
      mostrarFoto(file) {
        let reader = new FileReader();
        reader.onload = (file) => {
          this.fotoMuestra = file.target.result;
        }
        reader.readAsDataURL(file);
      },
// Modify your registrarPersona method to handle the duplicate username and email errors

registrarPersona() {
  if (this.validarPersona()) {
    return;
  }
  let me = this;
  let formData = new FormData();
  formData.append('nombre', this.nombre);
  formData.append('tipo_documento', this.tipo_documento);
  formData.append('num_documento', this.num_documento);
  formData.append('direccion', this.direccion);
  formData.append('telefono', this.telefono);
  formData.append('email', this.email);
  formData.append('idrol', this.idrol);
  formData.append('idsucursal', this.idsucursal);
  formData.append('usuario', this.usuario);
  formData.append('password', this.password);
  formData.append('fotografia', this.fotografia);

  axios.post('/user/registrar', formData, {
    headers: {
      'Content-Type': 'multipart/form-data'
    }
  }).then(function (response) {
    me.cerrarModal();
    me.listarPersona(1, '', 'nombre');
    swal(
      'Registrado!',
      'El usuario ha sido registrado con éxito.',
      'success'
    )
  }).catch(function (error) {
    console.log(error);
    // Check if the error response contains information about duplicate
    if (error.response && error.response.data) {
      if (error.response.data.error === 'duplicate_username') {
        swal({
          title: 'Error!',
          text: 'El usuario ya existe con ese nombre. Intente con otro nombre de usuario.',
          type: 'error',
          confirmButtonText: 'Entendido'
        });
      } else if (error.response.data.error === 'duplicate_email') {
        swal({
          title: 'Error!',
          text: 'Ya existe un usuario registrado con ese email. Intente con otro email.',
          type: 'error',
          confirmButtonText: 'Entendido'
        });
      } else {
        swal({
          title: 'Error!',
          text: 'Ocurrió un error al registrar el usuario.',
          type: 'error',
          confirmButtonText: 'Entendido'
        });
      }
    } else {
      swal({
        title: 'Error!',
        text: 'Ocurrió un error al registrar el usuario.',
        type: 'error',
        confirmButtonText: 'Entendido'
      });
    }
  });
},

// Similarly, update the actualizarPersona method to handle duplicate username and email errors
actualizarPersona() {
  if (this.validarPersona()) {
    return;
  }
  console.log(this.fotografia);
  let me = this;
  let formData = new FormData();
  formData.append('nombre', this.nombre);
  formData.append('tipo_documento', this.tipo_documento);
  formData.append('num_documento', this.num_documento);
  formData.append('direccion', this.direccion);
  formData.append('telefono', this.telefono);
  formData.append('email', this.email);
  formData.append('idrol', this.idrol);
  formData.append('idsucursal', this.idsucursal);
  formData.append('usuario', this.usuario);
  formData.append('password', this.password);
  formData.append('fotografia', this.fotografia);
  formData.append('id', this.persona_id);

  axios.post('/user/actualizar', formData, {
    headers: {
      'Content-Type': 'multipart/form-data'
    }
  }).then(function (response) {
    swal(
      'Actualizado!',
      'Datos actualizados con éxito',
      'success'
    );
    me.cerrarModal();
    me.listarPersona(1, '', 'nombre');
  }).catch(function (error) {
    console.log(error);
    // Check if the error response contains information about duplicates
    if (error.response && error.response.data) {
      if (error.response.data.error === 'duplicate_username') {
        swal({
          title: 'Error!',
          text: 'El usuario ya existe con ese nombre. Intente con otro nombre de usuario.',
          type: 'error',
          confirmButtonText: 'Entendido'
        });
      } else if (error.response.data.error === 'duplicate_email') {
        swal({
          title: 'Error!',
          text: 'Ya existe un usuario registrado con ese email. Intente con otro email.',
          type: 'error',
          confirmButtonText: 'Entendido'
        });
      } else {
        swal({
          title: 'Error!',
          text: 'Ocurrió un error al actualizar el usuario.',
          type: 'error',
          confirmButtonText: 'Entendido'
        });
      }
    } else {
      swal({
        title: 'Error!',
        text: 'Ocurrió un error al actualizar el usuario.',
        type: 'error',
        confirmButtonText: 'Entendido'
      });
    }
  });
},
      validarPersona() {
        this.errorPersona = 0;
        this.errorMostrarMsjPersona = [];
  
        if (!this.nombre) this.errorMostrarMsjPersona.push("El nombre de la pesona no puede estar vacío.");
        if (!this.usuario) this.errorMostrarMsjPersona.push("El nombre de usuario no puede estar vacío.");
        if (!this.password) this.errorMostrarMsjPersona.push("La password del usuario no puede estar vacía.");
        if (this.idrol == 0) this.errorMostrarMsjPersona.push("Seleccione una Role.");
        if (this.errorMostrarMsjPersona.length) this.errorPersona = 1;
  
        return this.errorPersona;
      },
      cerrarModal() {
        let fileInput = this.$refs.fotografiaInput;
        if (fileInput) {
          fileInput.value = '';
        }
  
        this.modal = false;
        this.tituloModal = '';
        this.nombre = '';
        this.tipo_documento = 'DNI';
        this.num_documento = '';
        this.direccion = '';
        this.telefono = '';
        this.email = '';
        this.usuario = '';
        this.password = '';
        this.fotografia = fileInput ? fileInput : '';
        this.fotoMuestra = 'img/usuarios/defecto.jpg';
        this.idrol = 0;
        this.idsucursal = 0;
        this.errorPersona = 0;
      },
      abrirModal(modelo, accion, data = []) {
        this.selectRol();
        this.selectSucursal();
        switch (modelo) {
          case "persona":
            {
              switch (accion) {
                case 'registrar':
                  {
                    this.modal = true;
                    this.tituloModal = 'Registrar Usuario';
                    this.nombre = '';
                    this.tipo_documento = 'DNI';
                    this.num_documento = '';
                    this.direccion = '';
                    this.telefono = '';
                    this.email = '';
                    this.usuario = '';
                    this.password = '';
                    this.fotografia = '';
                    this.idrol = 0;
                    this.idsucursal = 0;
                    this.tipoAccion = 1;
                    break;
                  }
                case 'actualizar':
                  {
                    this.modal = true;
                    this.tituloModal = 'Actualizar Usuario';
                    this.tipoAccion = 2;
                    this.persona_id = data['id'];
                    this.nombre = data['nombre'];
                    this.tipo_documento = data['tipo_documento'];
                    this.num_documento = data['num_documento'];
                    this.direccion = data['direccion'];
                    this.telefono = data['telefono'];
                    this.email = data['email'];
                    this.usuario = data['usuario'];
                    this.password = data['password'];
                    this.fotografia = data['fotografia'];
                    this.fotoMuestra = data['fotografia'] ? 'img/usuarios/' + data['fotografia'] : 'img/usuarios/defecto.jpg';
                    this.idrol = data['idrol'];
                    this.idsucursal = data['idsucursal'];
                    break;
                  }
              }
            }
        }
      },
        desactivarUsuario(id) {
            swal({
                title: 'Esta seguro de desactivar este usuario?',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Aceptar!',
                cancelButtonText: 'Cancelar',
                confirmButtonClass: 'btn btn-success',
                cancelButtonClass: 'btn btn-danger',
                buttonsStyling: false,
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    let me = this;

                    axios.put('/user/desactivar', {
                        'id': id
                    }).then(function (response) {
                        me.listarPersona(1, '', 'nombre');
                        swal(
                            'Desactivado!',
                            'El registro ha sido desactivado con éxito.',
                            'success'
                        )
                    }).catch(function (error) {
                        console.log(error);
                    });


                } else if (
                    // Read more about handling dismissals
                    result.dismiss === swal.DismissReason.cancel
                ) {

                }
            })
        },
        activarUsuario(id) {
            swal({
                title: 'Esta seguro de activar este usuario?',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Aceptar!',
                cancelButtonText: 'Cancelar',
                confirmButtonClass: 'btn btn-success',
                cancelButtonClass: 'btn btn-danger',
                buttonsStyling: false,
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    let me = this;

                    axios.put('/user/activar', {
                        'id': id
                    }).then(function (response) {
                        me.listarPersona(1, '', 'nombre');
                        swal(
                            'Activado!',
                            'El registro ha sido activado con éxito.',
                            'success'
                        )
                    }).catch(function (error) {
                        console.log(error);
                    });


                } else if (
                    // Read more about handling dismissals
                    result.dismiss === swal.DismissReason.cancel
                ) {

                }
            })
        },
        cargarReporteUsuariosExcel() {
            window.open('/user/listarReporteUsuariosExcel', '_blank');
        }
    },
    mounted() {
        this.listarPersona(1, this.buscar, this.criterio);
    }
}
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
