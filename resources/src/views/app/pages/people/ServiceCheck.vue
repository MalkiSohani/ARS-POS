<template>
  <div class="main-content">
    <breadcumb page="Service Check Management" folder="Service Check"/>

    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>

    <div v-else>
      <vue-good-table
        mode="remote"
        :columns="columns"
        :totalRows="totalRows"
        :rows="serviceChecks"
        @on-page-change="onPageChange"
        @on-per-page-change="onPerPageChange"
        @on-sort-change="onSortChange"
        @on-search="onSearch"
        :search-options="{
          enabled: true,
          placeholder: 'Search this table',
        }"
        :select-options="{
          enabled: true,
          clearSelectionText: '',
        }"
        @on-selected-rows-change="selectionChanged"
        :pagination-options="{
          enabled: true,
          mode: 'records',
          nextLabel: 'next',
          prevLabel: 'prev',
        }"

        :styleClass="'tableOne table-hover vgt-table'"
      >
        <div slot="selected-row-actions">
          <button class="btn btn-danger btn-sm" @click="deleteBySelected()">Delete</button>
        </div>

        <div slot="table-actions" class="mt-2 mb-3">
          <b-button
            @click="newServiceCheck()"
            size="sm"
            variant="btn btn-primary btn-icon m-1"
            v-if="currentUserPermissions && currentUserPermissions.includes('service_check_add')"
          >
            <i class="i-Add"></i>
            Add Service Check
          </b-button>
        </div>

        <template slot="table-row" slot-scope="props">
          <span v-if="props.column.field == 'actions'">
            <div>
              <b-dropdown
                id="dropdown-right"
                variant="link"
                toggle-class="text-decoration-none"
                size="lg"
                right
                no-caret
              >
                <template v-slot:button-content>
                  <span class="_dot _r_block-dot bg-dark"></span>
                  <span class="_dot _r_block-dot bg-dark"></span>
                  <span class="_dot _r_block-dot bg-dark"></span>
                </template>

                <b-dropdown-item
                  v-if="currentUserPermissions && currentUserPermissions.includes('service_check_edit')"
                  @click="editServiceCheck(props.row)"
                >
                  <i class="nav-icon i-Edit font-weight-bold mr-2"></i>
                  Edit
                </b-dropdown-item>

                <b-dropdown-item
                  v-if="currentUserPermissions && currentUserPermissions.includes('service_check_delete')"
                  @click="removeServiceCheck(props.row.id)"
                >
                  <i class="nav-icon i-Close-Window font-weight-bold mr-2"></i>
                  Delete
                </b-dropdown-item>
              </b-dropdown>
            </div>
          </span>
        </template>
      </vue-good-table>
    </div>

    <!-- Modal Create & Edit Service Check -->
    <validation-observer ref="CreateServiceCheck">
      <b-modal
        hide-footer
        size="md"
        id="ServiceCheckModal"
        :title="editmode ? 'Edit Service Check' : 'Create Service Check'"
      >
        <b-form @submit.prevent="submitServiceCheck">
          <b-row>

            <!-- Name -->
            <b-col md="12" sm="12">
              <validation-provider
                name="Name"
                :rules="{ required: true }"
                v-slot="validationContext"
              >
                <b-form-group label="Name *">
                  <b-form-input
                    :state="getValidationState(validationContext)"
                    placeholder="Enter Name"
                    v-model="serviceCheck.name"
                  ></b-form-input>
                  <b-form-invalid-feedback>{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>

            <!-- Employee Number -->
            <b-col md="12" sm="12">
              <validation-provider
                name="Employee Number"
                :rules="{ required: true }"
                v-slot="validationContext"
              >
                <b-form-group label="Employee Number *">
                  <b-form-input
                    :state="getValidationState(validationContext)"
                    placeholder="Enter Employee Number"
                    v-model="serviceCheck.employee_number"
                  ></b-form-input>
                  <b-form-invalid-feedback>{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>

            <!-- Contact Number -->
            <b-col md="12" sm="12">
              <validation-provider
                name="Contact Number"
                :rules="{ required: true }"
                v-slot="validationContext"
              >
                <b-form-group label="Contact Number *">
                  <b-form-input
                    :state="getValidationState(validationContext)"
                    placeholder="Enter Contact Number"
                    v-model="serviceCheck.contact_number"
                  ></b-form-input>
                  <b-form-invalid-feedback>{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>

            <b-col md="12" class="mt-3">
              <b-button variant="primary" type="submit" :disabled="submitProcessing">
                Submit
              </b-button>
              <div v-if="submitProcessing" class="spinner sm spinner-primary mt-3"></div>
            </b-col>
          </b-row>
        </b-form>
      </b-modal>
    </validation-observer>
  </div>
</template>

<script>
import { mapGetters } from "vuex";
import NProgress from "nprogress";

export default {
  metaInfo: {
    title: "Service Check"
  },
  name: "ServiceCheck",
  data() {
    return {
      isLoading: true,
      submitProcessing: false,
      showDropdown: false,
      serverParams: {
        columnFilters: {},
        sort: {
          field: "id",
          type: "desc"
        },
        page: 1,
        perPage: 10
      },
      selectedIds: [],
      totalRows: 0,
      search: "",
      limit: "10",
      serviceChecks: [],
      editmode: false,
      serviceCheck: {
        id: "",
        name: "",
        employee_number: "",
        contact_number: ""
      }
    };
  },

  mounted() {
    this.$root.$on("bv::dropdown::show", () => {
      this.showDropdown = true;
    });
    this.$root.$on("bv::dropdown::hide", () => {
      this.showDropdown = false;
    });
  },

  computed: {
    ...mapGetters(["currentUserPermissions", "currentUser"]),

    columns() {
      return [
        {
          label: "Name",
          field: "name",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: "Employee Number",
          field: "employee_number",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: "Contact Number",
          field: "contact_number",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: "Date",
          field: "created_at",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: "Action",
          field: "actions",
          tdClass: "text-right",
          thClass: "text-right",
          sortable: false
        }
      ];
    }
  },

  methods: {
    updateParams(newProps) {
      this.serverParams = Object.assign({}, this.serverParams, newProps);
    },

    onPageChange({ currentPage }) {
      if (this.serverParams.page !== currentPage) {
        this.updateParams({ page: currentPage });
        this.getServiceChecks(currentPage);
      }
    },

    onPerPageChange({ currentPerPage }) {
      if (this.limit !== currentPerPage) {
        this.limit = currentPerPage;
        this.updateParams({ page: 1, perPage: currentPerPage });
        this.getServiceChecks(1);
      }
    },

    selectionChanged({ selectedRows }) {
      this.selectedIds = selectedRows.map(row => row.id);
    },

    onSortChange(params) {
      this.updateParams({
        sort: {
          type: params[0].type,
          field: params[0].field
        }
      });
      this.getServiceChecks(this.serverParams.page);
    },

    onSearch(value) {
      this.search = value.searchTerm;
      this.getServiceChecks(this.serverParams.page);
    },

    getValidationState({ dirty, validated, valid = null }) {
      return dirty || validated ? valid : null;
    },

    makeToast(variant, msg, title) {
      this.$root.$bvToast.toast(msg, {
        title: title,
        variant: variant,
        solid: true
      });
    },

    getServiceChecks(page) {
      NProgress.start();
      NProgress.set(0.1);

      axios.get(`service_checks?page=${page}&SortField=${this.serverParams.sort.field}&SortType=${this.serverParams.sort.type}&search=${this.search}&limit=${this.limit}`)
        .then(response => {
          this.serviceChecks = response.data.service_checks;
          this.totalRows = response.data.totalRows;
          NProgress.done();
          this.isLoading = false;
        })
        .catch(error => {
          NProgress.done();
          this.isLoading = false;
        });
    },

    newServiceCheck() {
      this.resetForm();
      this.editmode = false;
      this.$bvModal.show("ServiceCheckModal");
    },

    editServiceCheck(row) {
      this.resetForm();
      this.serviceCheck = { ...row };
      this.editmode = true;
      this.$bvModal.show("ServiceCheckModal");
    },

    submitServiceCheck() {
      this.$refs.CreateServiceCheck.validate().then(success => {
        if (!success) {
          this.makeToast("danger", "Please fill the form correctly", "Failed");
        } else {
          if (!this.editmode) {
            this.createServiceCheck();
          } else {
            this.updateServiceCheck();
          }
        }
      });
    },

    createServiceCheck() {
      this.submitProcessing = true;
      axios.post("service_checks", this.serviceCheck)
        .then(response => {
          Fire.$emit("EventServiceCheck");
          this.makeToast("success", "Successfully Created", "Success");
          this.submitProcessing = false;
        })
        .catch(error => {
          this.makeToast("danger", "Invalid Data", "Failed");
          this.submitProcessing = false;
        });
    },

    updateServiceCheck() {
      this.submitProcessing = true;
      axios.put(`service_checks/${this.serviceCheck.id}`, this.serviceCheck)
        .then(response => {
          Fire.$emit("EventServiceCheck");
          this.makeToast("success", "Successfully Updated", "Success");
          this.submitProcessing = false;
        })
        .catch(error => {
          this.makeToast("danger", "Invalid Data", "Failed");
          this.submitProcessing = false;
        });
    },

    resetForm() {
      this.serviceCheck = {
        id: "",
        name: "",
        employee_number: "",
        contact_number: ""
      };
    },

    removeServiceCheck(id) {
      this.$swal({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: "Cancel",
        confirmButtonText: "Yes, delete it!"
      }).then(result => {
        if (result.value) {
          axios.delete(`service_checks/${id}`)
            .then(() => {
              this.$swal("Deleted!", "Record has been deleted.", "success");
              Fire.$emit("DeleteServiceCheck");
            })
            .catch(() => {
              this.$swal("Failed!", "Something went wrong.", "warning");
            });
        }
      });
    },

    deleteBySelected() {
      this.$swal({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: "Cancel",
        confirmButtonText: "Yes, delete them!"
      }).then(result => {
        if (result.value) {
          NProgress.start();
          NProgress.set(0.1);
          axios.post("service_checks/delete/by_selection", {
              selectedIds: this.selectedIds
            })
            .then(() => {
              this.$swal("Deleted!", "Records have been deleted.", "success");
              Fire.$emit("DeleteServiceCheck");
            })
            .catch(() => {
              NProgress.done();
              this.$swal("Failed!", "Something went wrong.", "warning");
            });
        }
      });
    }
  },

  created() {
    this.getServiceChecks(1);

    Fire.$on("EventServiceCheck", () => {
      setTimeout(() => {
        this.getServiceChecks(this.serverParams.page);
        this.$bvModal.hide("ServiceCheckModal");
      }, 500);
    });

    Fire.$on("DeleteServiceCheck", () => {
      setTimeout(() => {
        this.getServiceChecks(this.serverParams.page);
      }, 500);
    });
  }
};
</script>
