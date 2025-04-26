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
            <Column field="telefono" header="Teléfono"></Column>
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
            <InputText class="p-inputtext-sm" id="nombre" v-model="nombre" :class="{'p-invalid': errors.nombre}" />
            <small v-if="errors.nombre" class="p-error">{{ errors.nombre }}</small>
          </div>
          <div class="p-field p-col-12 p-md-6">
            <label for="tipo_documento">Tipo documento(*)</label>
            <Dropdown class="p-inputtext-sm" id="tipo_documento" v-model="tipo_documento" 
                      :options="tipoDocumentoOptions" optionLabel="label" optionValue="value" 
                      placeholder="Selecciona un tipo de documento" 
                      :class="{'p-invalid': errors.tipo_documento}" />
            <small v-if="errors.tipo_documento" class="p-error">{{ errors.tipo_documento }}</small>
          </div>
          <div class="p-field p-col-12 p-md-6">
            <label for="num_documento">Número documento(*)</label>
            <InputText class="p-inputtext-sm" id="num_documento" v-model="num_documento" 
                      :class="{'p-invalid': errors.num_documento}" />
            <small v-if="errors.num_documento" class="p-error">{{ errors.num_documento }}</small>
          </div>
          <div class="p-field p-col-12 p-md-6">
            <label for="telefono">Teléfono(*)</label>
            <InputText class="p-inputtext-sm" id="telefono" v-model="telefono" 
                      :class="{'p-invalid': errors.telefono}" />
            <small v-if="errors.telefono" class="p-error">{{ errors.telefono }}</small>
          </div>
          <div class="p-field p-col-12 p-md-6">
            <label for="idrol">Rol(*)</label>
            <Dropdown class="p-inputtext-sm" id="idrol" v-model="idrol" 
                      :options="arrayRol" optionLabel="nombre" optionValue="id" 
                      placeholder="Seleccione" :class="{'p-invalid': errors.idrol}" />
            <small v-if="errors.idrol" class="p-error">{{ errors.idrol }}</small>
          </div>
          <div class="p-field p-col-12 p-md-6">
            <label for="idsucursal">Sucursal(*)</label>
            <Dropdown class="p-inputtext-sm" id="idsucursal" v-model="idsucursal" 
                      :options="arraySucursal" optionLabel="nombre" optionValue="id" 
                      placeholder="Seleccione" :class="{'p-invalid': errors.idsucursal}" />
            <small v-if="errors.idsucursal" class="p-error">{{ errors.idsucursal }}</small>
          </div>
          <div class="p-field p-col-12 p-md-6">
            <label for="usuario">Usuario(*)</label>
            <InputText class="p-inputtext-sm" id="usuario" v-model="usuario" 
                      :class="{'p-invalid': errors.usuario}" />
            <small v-if="errors.usuario" class="p-error">{{ errors.usuario }}</small>
          </div>
          <div class="p-field p-col-12 p-md-6">
            <label for="password">Clave(*)</label>
            <Password class="p-inputtext-sm" id="password" v-model="password" 
                      :class="{'p-invalid': errors.password}" toggleMask :feedback="false" />
            <small v-if="errors.password" class="p-error">{{ errors.password }}</small>
          </div>
        </div>
  
        <template #footer>
          <Button label="Cerrar" icon="pi pi-times" @click="cerrarModal" class="p-button-danger"/>
          <Button v-if="tipoAccion == 1" label="Guardar" icon="pi pi-check" @click="registrarPersona"  class="p-button-success"/>
          <Button v-if="tipoAccion == 2" label="Actualizar" icon="pi pi-check" @click="actualizarPersona"  class="p-button-success"/>
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
          {label: 'Teléfono', value: 'telefono'},
          {label: 'Sucursal', value: 'nombre'}
        ],
        loading: false,
        persona_id: 0,
        nombre: '',
        tipo_documento: '',
        num_documento: '',
        telefono: '',
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
        errors: {},
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
    methods: {
      onUpload(event) {
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
      
      validarFormulario() {
        this.errors = {};
        
        // Validación de nombre
        if (!this.nombre) {
          this.errors.nombre = 'El nombre es obligatorio';
        } else if (this.nombre.length < 3) {
          this.errors.nombre = 'El nombre debe tener al menos 3 caracteres';
        }
        
        // Validación de tipo de documento
        if (!this.tipo_documento) {
          this.errors.tipo_documento = 'Seleccione un tipo de documento';
        }
        
        // Validación de número de documento
        if (!this.num_documento) {
          this.errors.num_documento = 'El número de documento es obligatorio';
        } else if (!/^\d+$/.test(this.num_documento)) {
          this.errors.num_documento = 'Solo se permiten números';
        } else if (this.num_documento.length < 4) {
          this.errors.num_documento = 'El documento debe tener al menos 4 dígitos';
        }
        
        // Validación de teléfono
        if (!this.telefono) {
          this.errors.telefono = 'El teléfono es obligatorio';
        } else if (!/^\d+$/.test(this.telefono)) {
          this.errors.telefono = 'Solo se permiten números';
        } else if (this.telefono.length < 7) {
          this.errors.telefono = 'El teléfono debe tener al menos 7 dígitos';
        }
        
        // Validación de rol
        if (!this.idrol) {
          this.errors.idrol = 'Seleccione un rol';
        }
        
        // Validación de sucursal
        if (!this.idsucursal) {
          this.errors.idsucursal = 'Seleccione una sucursal';
        }
        
        // Validación de usuario
        if (!this.usuario) {
          this.errors.usuario = 'El usuario es obligatorio';
        } else if (this.usuario.length < 4) {
          this.errors.usuario = 'El usuario debe tener al menos 4 caracteres';
        } else if (!/^[a-zA-Z0-9_]+$/.test(this.usuario)) {
          this.errors.usuario = 'Solo se permiten letras, números y guiones bajos';
        }
        
        // Validación de contraseña
        if (this.tipoAccion === 1 && !this.password) {
          this.errors.password = 'La contraseña es obligatoria';
        } else if (this.password && this.password.length < 6) {
          this.errors.password = 'La contraseña debe tener al menos 6 caracteres';
        }
        
        return Object.keys(this.errors).length === 0;
      },
      registrarPersona() {
  let me = this;
  let formData = new FormData();
  
  // Agregar campos al formData
  formData.append('nombre', this.nombre);
  formData.append('tipo_documento', this.tipo_documento);
  formData.append('num_documento', this.num_documento);
  formData.append('telefono', this.telefono);
  formData.append('usuario', this.usuario);
  formData.append('password', this.password);
  formData.append('idrol', this.idrol);
  formData.append('idsucursal', this.idsucursal);
  
  if (this.fotografia) {
    formData.append('fotografia', this.fotografia);
  }

  axios.post('/user/registrar', formData, {
    headers: {
      'Content-Type': 'multipart/form-data'
    }
  }).then(response => {
    if (response.data && response.data.success) {
      swal({
        title: 'Éxito',
        text: response.data.message,
        icon: 'success',
        button: 'Aceptar'
      });
      this.cerrarModal();
      this.listarPersona(1, '', 'nombre');
    }
  }).catch(error => {
    let errorTitle = 'Error';
    let errorHtml = '<div style="text-align:left;">';
    
    if (error.response && error.response.status === 422) {
      const errors = error.response.data.errors;
      const duplicateErrors = {};
      
      // Filtrar solo los errores de duplicados
      if (errors.nombre && errors.nombre.includes('ya está registrado')) {
        duplicateErrors.nombre = errors.nombre;
      }
      if (errors.num_documento && errors.num_documento.includes('ya está registrado')) {
        duplicateErrors.num_documento = errors.num_documento;
      }
      if (errors.usuario && errors.usuario.includes('ya está en uso')) {
        duplicateErrors.usuario = errors.usuario;
      }
      
      // Mostrar solo si hay errores de duplicados
      if (Object.keys(duplicateErrors).length > 0) {
        errorTitle = 'Datos duplicados';
        errorHtml += '<ul style="margin:0;padding-left:20px;">';
        
        for (const key in duplicateErrors) {
          errorHtml += `<li>${duplicateErrors[key]}</li>`;
        }
        
        errorHtml += '</ul>';
      } else {
        // Si no hay duplicados pero hay otros errores, mostrar mensaje genérico
        errorHtml += 'Por favor complete todos los campos requeridos correctamente';
      }
    } else {
      errorHtml += 'Ocurrió un error al registrar el usuario';
    }
    
    errorHtml += '</div>';
    
    swal({
      title: errorTitle,
      html: errorHtml,
      icon: 'error',
      confirmButtonText: 'Entendido'
    });
  });
},
// Método para traducir nombres de campos
getFieldName(field) {
  const fieldNames = {
    'nombre': 'Nombre',
    'tipo_documento': 'Tipo de documento',
    'num_documento': 'Número de documento',
    'telefono': 'Teléfono',
    'usuario': 'Usuario',
    'password': 'Contraseña',
    'idrol': 'Rol',
    'idsucursal': 'Sucursal'
  };
  return fieldNames[field] || field;
},
validarFormulario() {
  this.errors = {};
  let isValid = true;

  if (!this.nombre) {
    this.errors.nombre = 'El nombre es obligatorio';
    isValid = false;
  }

  if (!this.tipo_documento) {
    this.errors.tipo_documento = 'Seleccione un tipo de documento';
    isValid = false;
  }

  if (!this.num_documento) {
    this.errors.num_documento = 'El número de documento es obligatorio';
    isValid = false;
  } else if (!/^\d+$/.test(this.num_documento)) {
    this.errors.num_documento = 'Solo se permiten números';
    isValid = false;
  }

  if (!this.telefono) {
    this.errors.telefono = 'El teléfono es obligatorio';
    isValid = false;
  } else if (!/^\d+$/.test(this.telefono)) {
    this.errors.telefono = 'Solo se permiten números';
    isValid = false;
  }

  if (!this.idrol) {
    this.errors.idrol = 'Seleccione un rol';
    isValid = false;
  }

  if (!this.idsucursal) {
    this.errors.idsucursal = 'Seleccione una sucursal';
    isValid = false;
  }

  if (this.tipoAccion === 1 && !this.password) {
    this.errors.password = 'La contraseña es obligatoria';
    isValid = false;
  } else if (this.password && this.password.length < 6) {
    this.errors.password = 'La contraseña debe tener al menos 6 caracteres';
    isValid = false;
  }

  if (!this.usuario) {
    this.errors.usuario = 'El usuario es obligatorio';
    isValid = false;
  } else if (this.usuario.length < 4) {
    this.errors.usuario = 'El usuario debe tener al menos 4 caracteres';
    isValid = false;
  }

  return isValid;
},
      
      actualizarPersona() {
        if (!this.validarFormulario()) {
          return;
        }
        
        let me = this;
        let formData = new FormData();
        formData.append('nombre', this.nombre);
        formData.append('tipo_documento', this.tipo_documento);
        formData.append('num_documento', this.num_documento);
        formData.append('telefono', this.telefono);
        formData.append('idrol', this.idrol);
        formData.append('idsucursal', this.idsucursal);
        formData.append('usuario', this.usuario);
        if (this.password) {
          formData.append('password', this.password);
        }
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
          if (error.response && error.response.data) {
            if (error.response.data.error === 'duplicate_username') {
              me.errors.usuario = 'El nombre de usuario ya existe';
            } else if (error.response.data.error === 'duplicate_document') {
              me.errors.num_documento = 'El número de documento ya está registrado';
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
      
      cerrarModal() {
        this.modal = false;
        this.tituloModal = '';
        this.nombre = '';
        this.tipo_documento = '';
        this.num_documento = '';
        this.telefono = '';
        this.usuario = '';
        this.password = '';
        this.fotografia = '';
        this.fotoMuestra = 'img/usuarios/defecto.jpg';
        this.idrol = '';
        this.idsucursal = '';
        this.errors = {};
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
                    this.telefono = data['telefono'];
                    this.usuario = data['usuario'];
                    this.password = '';
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