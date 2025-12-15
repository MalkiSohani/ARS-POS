<template>
  <div class="main-content mui-form">
    <breadcumb :page="$t('CustomerManagement')" :folder="$t('Customers')"/>
    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>
    <div v-else>
      <div class="mb-5">
        <span class="alert alert-danger" v-show="clients_without_ecommerce > 0">
          {{$t('There_are')}} <strong>{{ clients_without_ecommerce}}</strong> 
          {{$t('Customers_without_ecommerce_notice')}}
          <router-link  to="/app/People/Customers_without_ecommerce">
          {{$t('View_Details')}}
          </router-link>
        </span>
      </div>

      <!-- Table remains the same -->
      <vue-good-table
        mode="remote"
        :columns="columns"
        :totalRows="totalRows"
        :rows="clients"
        @on-page-change="onPageChange"
        @on-per-page-change="onPerPageChange"
        @on-sort-change="onSortChange"
        @on-search="onSearch"
        :search-options="{
        enabled: true,
        placeholder: $t('Search_this_table'),  
      }"
        :select-options="{ 
          enabled: true ,
          clearSelectionText: '',
        }"
        @on-selected-rows-change="selectionChanged"
        :pagination-options="{
        enabled: true,
        mode: 'records',
        nextLabel: 'next',
        prevLabel: 'prev',
      }"
       :styleClass="showDropdown?'tableOne table-hover vgt-table full-height':'tableOne table-hover vgt-table non-height'"
      >
        <div slot="selected-row-actions">
          <button class="btn btn-danger btn-sm" @click="delete_by_selected()">{{$t('Del')}}</button>
        </div>
        <div slot="table-actions" class="mt-2 mb-3">
          <b-button variant="outline-info m-1" size="sm" v-b-toggle.sidebar-right>
            <i class="i-Filter-2"></i>
            {{ $t("Filter") }}
          </b-button>
          <b-button @click="clients_PDF()" size="sm" variant="outline-success m-1">
            <i class="i-File-Copy"></i> PDF
          </b-button>
           <vue-excel-xlsx
              class="btn btn-sm btn-outline-danger ripple m-1"
              :data="clients"
              :columns="columns"
              :file-name="'clients'"
              :file-type="'xlsx'"
              :sheet-name="'clients'"
              >
              <i class="i-File-Excel"></i> EXCEL
          </vue-excel-xlsx>
         <router-link
            v-if="currentUserPermissions && currentUserPermissions.includes('customers_import')"
            :to="{ name: 'Import_Customers' }"
            class="btn btn-info btn-sm m-1"
          >
            <i class="i-Download"></i>
            Import Customers
          </router-link>
          <b-button
            @click="New_Client()"
            size="sm"
            variant="btn btn-primary btn-icon m-1"
            v-if="currentUserPermissions && currentUserPermissions.includes('Customers_add')"
          >
            <i class="i-Add"></i>
            {{$t('Add')}}
          </b-button>
        </div>

        <template slot="table-row" slot-scope="props">
          <span v-if="props.column.field == 'actions'">
            <div>
              <b-dropdown
                id="dropdown-right"
                variant="link"
                text="right align"
                toggle-class="text-decoration-none"
                size="lg"
                right
                no-caret
              >
                <template v-slot:button-content class="_r_btn border-0">
                  <span class="_dot _r_block-dot bg-dark"></span>
                  <span class="_dot _r_block-dot bg-dark"></span>
                  <span class="_dot _r_block-dot bg-dark"></span>
                </template>

                 <b-dropdown-item @click="$router.push({ name: 'CustomerLedger', params: { id: props.row.id } })">
                  <i class="nav-icon i-Receipt font-weight-bold mr-2"></i>
                 {{$t('Customer_Ledger')}}
                </b-dropdown-item>

                <b-dropdown-item
                  @click="showDetails(props.row)"
                >
                  <i class="nav-icon i-Eye font-weight-bold mr-2"></i>
                  {{$t('Customer_details')}}
                </b-dropdown-item>

                <b-dropdown-item @click="openPointsModal(props.row)">
                  <i class="nav-icon i-Edit font-weight-bold mr-2"></i>
                  {{$t('Adjust_Customer_Points')}}
                </b-dropdown-item>
               
                <b-dropdown-item
                  @click="show_credit_card_details(props.row.id)"
                >
                  <i class="nav-icon i-Credit-Card2 font-weight-bold mr-2"></i>
                  {{$t('Credit_Card_Info')}}
                </b-dropdown-item>

                <b-dropdown-item
                 v-if="currentUserPermissions && currentUserPermissions.includes('Customers_edit')"
                  @click="Edit_Client(props.row)"
                >
                  <i class="nav-icon i-Edit font-weight-bold mr-2"></i>
                  {{$t('Edit_Customer')}}
                </b-dropdown-item>

                <b-dropdown-item
                  title="Delete"
                  v-if="currentUserPermissions.includes('Customers_delete')"
                  @click="Remove_Client(props.row.id)"
                >
                  <i class="nav-icon i-Close-Window font-weight-bold mr-2"></i>
                  {{$t('Delete_Customer')}}
                </b-dropdown-item>
                </b-dropdown>
            </div>
          </span>
        </template>

      </vue-good-table>
    </div>

    <!-- Multiple filters -->
    <b-sidebar id="sidebar-right" :title="$t('Filter')" bg-variant="white" right shadow>
      <div class="px-3 py-2">
        <b-row>
          <!-- Code Customer   -->
          <b-col md="12">
            <b-form-group :label="$t('CustomerCode')">
              <b-form-input label="Code" :placeholder="$t('SearchByCode')" v-model="Filter_Code"></b-form-input>
            </b-form-group>
          </b-col>

          <!-- Name Customer   -->
          <b-col md="12">
            <b-form-group :label="$t('CustomerName')">
              <b-form-input label="Name" :placeholder="$t('SearchByName')" v-model="Filter_Name"></b-form-input>
            </b-form-group>
          </b-col>

          <!-- Phone Customer   -->
          <b-col md="12">
            <b-form-group :label="$t('Phone')">
              <b-form-input label="Phone" :placeholder="$t('SearchByPhone')" v-model="Filter_Phone"></b-form-input>
            </b-form-group>
          </b-col>

          <!-- Email Customer   -->
          <b-col md="12">
            <b-form-group :label="$t('Email')">
              <b-form-input label="Email" :placeholder="$t('SearchByEmail')" v-model="Filter_Email"></b-form-input>
            </b-form-group>
          </b-col>

          <b-col md="6" sm="12">
            <b-button @click="Get_Clients(serverParams.page)" variant="primary m-1" size="sm" block>
              <i class="i-Filter-2"></i>
              {{ $t("Filter") }}
            </b-button>
          </b-col>
          <b-col md="6" sm="12">
            <b-button @click="Reset_Filter()" variant="danger m-1" size="sm" block>
              <i class="i-Power-2"></i>
              {{ $t("Reset") }}
            </b-button>
          </b-col>
        </b-row>
      </div>
    </b-sidebar>

    <!-- Modal Create & Edit Customer - Material-UI Style -->
    <validation-observer ref="Create_Customer">
      <b-modal hide-footer size="lg" id="New_Customer" :title="editmode?$t('Edit'):$t('Add')">
        <b-form @submit.prevent="Submit_Customer" class="mui-form">
          
          <!-- Material-UI Grid Container -->
          <mui-grid container :spacing="3">
            
            <!-- Customer Name -->
            <mui-grid item :xs="12" :sm="6">
              <validation-provider
                name="Name Customer"
                :rules="{ required: true}"
                v-slot="validationContext"
              >
                <mui-text-field
                  :label="$t('CustomerName')"
                  v-model="client.name"
                  :required="true"
                  :error="!!validationContext.errors[0]"
                  :error-message="validationContext.errors[0]"
                />
              </validation-provider>
            </mui-grid>
            
            <!-- Customer Email -->
            <mui-grid item :xs="12" :sm="6">
              <mui-text-field
                :label="$t('Email')"
                v-model="client.email"
                type="email"
              />
            </mui-grid>

            <!-- Customer Phone -->
            <mui-grid item :xs="12" :sm="6">
              <mui-text-field
                :label="$t('Phone')"
                v-model="client.phone"
                type="tel"
              />
            </mui-grid>

            <!-- Customer Country -->
            <mui-grid item :xs="12" :sm="6">
              <mui-text-field
                :label="$t('Country')"
                v-model="client.country"
              />
            </mui-grid>

            <!-- Customer City -->
            <mui-grid item :xs="12" :sm="6">
              <mui-text-field
                :label="$t('City')"
                v-model="client.city"
              />
            </mui-grid>

            <!-- Customer Tax Number -->
            <mui-grid item :xs="12" :sm="6">
              <mui-text-field
                :label="$t('Tax_Number')"
                v-model="client.tax_number"
              />
            </mui-grid>

            <!-- Customer Address -->
            <mui-grid item :xs="12">
              <mui-text-field
                :label="$t('Adress')"
                v-model="client.adresse"
                :textarea="true"
                :rows="4"
              />
            </mui-grid>

            <!-- Royalty Eligible Checkbox -->
            <mui-grid item :xs="12" :sm="6">
              <div class="psx-form-check mt-2">
                <input type="checkbox" v-model="client.is_royalty_eligible" class="psx-checkbox psx-form-check-input" id="is_royalty_eligible">
                <label class="psx-form-check-label" for="is_royalty_eligible">
                  <h5>{{ $t('Is_Royalty_Eligible') }}</h5>
                </label>
              </div>
            </mui-grid>

            <!-- Action Buttons -->
            <mui-grid item :xs="12">
              <div class="mui-mt-2" style="display: flex; gap: 12px;">
                <mui-button 
                  variant="contained" 
                  color="primary" 
                  type="submit"
                  :disabled="SubmitProcessing"
                >
                  {{$t('submit')}}
                </mui-button>
                <mui-button 
                  variant="outlined" 
                  color="secondary" 
                  type="button"
                  @click="$bvModal.hide('New_Customer')"
                >
                  {{$t('Cancel')}}
                </mui-button>
              </div>
              <div v-once class="typo__p" v-if="SubmitProcessing">
                <div class="spinner sm spinner-primary mt-3"></div>
              </div>
            </mui-grid>

          </mui-grid>
        </b-form>
      </b-modal>
    </validation-observer>

    <!-- Rest of modals remain unchanged for now -->
    <b-modal
      id="adjust-points-modal"
      ref="pointsModal"
      title="Adjust Customer Points"
      hide-footer
    >
      <b-form @submit.prevent="updateCustomerPoints">
        <b-form-group label="Points">
          <b-form-input
            v-model.number="adjustPointsForm.points"
            type="number"
            required
          />
        </b-form-group>

        <div class="text-right">
          <b-button variant="secondary" @click="$refs.pointsModal.hide()">
            {{ $t('Cancel') }}
          </b-button>
          <b-button variant="primary" type="submit">
             {{ $t('Save') }}
          </b-button>
        </div>
      </b-form>
    </b-modal>

    <!-- Modal Show Customer Details -->
    <b-modal ok-only size="md" id="showDetails" :title="$t('CustomerDetails')">
      <b-row>
        <b-col lg="12" md="12" sm="12" class="mt-3">
          <table class="table table-striped table-md">
            <tbody>
              <tr>
                <td>{{$t('CustomerCode')}}</td>
                <th>{{client.code}}</th>
              </tr>
              <tr>
                <td>{{$t('CustomerName')}}</td>
                <th>{{client.name}}</th>
              </tr>
              <tr>
                <td>{{$t('Phone')}}</td>
                <th>{{client.phone}}</th>
              </tr>
              <tr>
                <td>{{$t('Email')}}</td>
                <th>{{client.email}}</th>
              </tr>
              <tr>
                <td>{{$t('Country')}}</td>
                <th>{{client.country}}</th>
              </tr>
              <tr>
                <td>{{$t('City')}}</td>
                <th>{{client.city}}</th>
              </tr>
              <tr>
                <td>{{$t('Adress')}}</td>
                <th>{{client.adresse}}</th>
              </tr>
              <tr>
                <td>{{$t('Tax_Number')}}</td>
                <th>{{client.tax_number}}</th>
              </tr>
              <tr>
                <td>{{$t('Points')}}</td>
                <th>{{client.points}}</th>
              </tr>
            </tbody>
          </table>
        </b-col>
      </b-row>
    </b-modal>

    <!-- Credit Card Modal -->
    <b-modal ok-only size="lg" id="show_credit_card_details" :title="$t('Saved_Credit_Card_Info')">
      <b-row>
        <b-col md="12" v-if="savedPaymentMethods && savedPaymentMethods.length > 0">
            <div class="mt-3"><span >Saved Credit Card Info For This Client </span></div>    
            <table class="table table-hover mt-3">
              <thead>
                <tr>
                  <th>Last 4 digits</th>
                  <th>Type</th>
                  <th>Exp</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="card in savedPaymentMethods" :class="{ 'bg-selected-card': isSelectedCard(card) }">
                  <td>**** {{card.last4}}</td>
                  <td>{{card.type}}</td>
                  <td>{{card.exp}}</td>
                  <td>
                      <b-button variant="outline-primary" @click="selectCard(card)" v-if="!isSelectedCard(card) && card_id != card.card_id">
                        <span>
                          <i class="i-Drag-Up"></i> 
                          Set as default
                        </span>
                      </b-button>
                        <span v-if="isSelectedCard(card) || card_id == card.card_id"><i class="i-Yes" style=" font-size: 20px; "></i> 
                        Default credit card  </span>
                  </td>
                </tr>
              </tbody>
            </table>
        </b-col>
        <b-col md="12" v-else>
            <div class="mt-3"><span >Customer don't have credit card saved </span></div>    
        </b-col>
      </b-row>
    </b-modal>

  </div>
</template>

<script>
import { mapActions, mapGetters } from "vuex";
import NProgress from "nprogress";
import jsPDF from "jspdf";
import autoTable from "jspdf-autotable";

export default {
  metaInfo: {
    title: "Customer"
  },
  data() {
    return {
      savedPaymentMethods: [],
      selectedCard:null,
      card_id:'',
      customer_id:'',
      clients_without_ecommerce:'',
      module_name:'',
      isLoading: true,
      SubmitProcessing:false,
      ImportProcessing:false,
      paymentProcessing:false,
      payment_return_Processing:false,
      serverParams: {
        columnFilters: {},
        sort: {
          field: "id",
          type: "desc"
        },
        page: 1,
        perPage: 10
      },
      showDropdown: false,
      payment: {
        client_id: "",
        client_name: "",
        account_id: "",
        date:"",
        due: "",
        amount: "",
        notes: "",
        payment_method_id: "",
      },
      payment_methods:[],
       payment_return: {
        client_id: "",
        client_name: "",
        account_id: "",
        date:"",
        return_Due: "",
        amount: "",
        notes: "",
        payment_method_id: "",
      },
      company_info:{},
      selectedIds: [],
      totalRows: "",
      search: "",
      limit: "10",
      Filter_Name: "",
      Filter_Code: "",
      Filter_Phone: "",
      Filter_Email: "",
      clients: [],
      accounts: [],
      editmode: false,
      import_clients: "",
      data: new FormData(),
      client: {
        id: "",
        name: "",
        code: "",
        email: "",
        phone: "",
        country: "",
        city: "",
        adresse: "",
        tax_number: "",
        is_royalty_eligible: "",
      },
      adjustPointsForm: {
        customer_id: null,
        points: 0
      },
      client_store: {
        name: "",
        email: "",
        password: "",
        NewPassword: null,
      },
      email_exist : "",
    };
  },

   mounted() {
    this.$root.$on("bv::dropdown::show", bvEvent => {
      this.showDropdown = true;
    });
    this.$root.$on("bv::dropdown::hide", bvEvent => {
      this.showDropdown = false;
    });
  },

  computed: {
    ...mapGetters(["currentUserPermissions", "currentUser"]),
    isSelectedCard() {
      return card => this.selectedCard === card;
    },
    columns() {
      return [
        {
          label: this.$t("Code"),
          field: "code",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Name"),
          field: "name",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Phone"),
          field: "phone",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Email"),
          field: "email",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Points"),
          field: "points",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Action"),
          field: "actions",
          tdClass: "text-right",
          thClass: "text-right",
          sortable: false
        }
      ];
    }
  },

  methods: {
    openPointsModal(customer) {
      this.adjustPointsForm.customer_id = customer.id;
      this.adjustPointsForm.points = customer.points;
      this.$refs.pointsModal.show();
    },

    updateCustomerPoints() {
      axios.post(`customers/${this.adjustPointsForm.customer_id}/update-points`, {
        points: this.adjustPointsForm.points
      }).then(() => {
        this.makeToast("success", 'Points updated successfully', 'success');
        this.$refs.pointsModal.hide();
        this.Get_Clients(this.serverParams.page);
      }).catch((error) => {
        this.makeToast("danger", 'Error updating points', this.$t("Failed"));
      });
    },

    Submit_Customer() {
      this.$refs.Create_Customer.validate().then(success => {
        if (!success) {
          this.makeToast("danger", this.$t("Please_fill_the_form_correctly"), this.$t("Failed"));
        } else {
          if (!this.editmode) {
            this.Create_Client();
          } else {
            this.Update_Client();
          }
        }
      });
    },

    updateParams(newProps) {
      this.serverParams = Object.assign({}, this.serverParams, newProps);
    },

    onPageChange({ currentPage }) {
      if (this.serverParams.page !== currentPage) {
        this.updateParams({ page: currentPage });
        this.Get_Clients(currentPage);
      }
    },

    onPerPageChange({ currentPerPage }) {
      if (this.limit !== currentPerPage) {
        this.limit = currentPerPage;
        this.updateParams({ page: 1, perPage: currentPerPage });
        this.Get_Clients(1);
      }
    },

    selectionChanged({ selectedRows }) {
      this.selectedIds = [];
      selectedRows.forEach((row, index) => {
        this.selectedIds.push(row.id);
      });
    },

    onSortChange(params) {
      this.updateParams({
        sort: {
          type: params[0].type,
          field: params[0].field
        }
      });
      this.Get_Clients(this.serverParams.page);
    },

    onSearch(value) {
      this.search = value.searchTerm;
      this.Get_Clients(this.serverParams.page);
    },

    getValidationState({ dirty, validated, valid = null }) {
      return dirty || validated ? valid : null;
    },

    Reset_Filter() {
      this.search = "";
      this.Filter_Name = "";
      this.Filter_Code = "";
      this.Filter_Phone = "";
      this.Filter_Email = "";
      this.Get_Clients(this.serverParams.page);
    },

    makeToast(variant, msg, title) {
      this.$root.$bvToast.toast(msg, {
        title: title,
        variant: variant,
        solid: true
      });
    },

    clients_PDF() {
      var self = this;
      let pdf = new jsPDF("p", "pt");
      const fontPath = "/fonts/Vazirmatn-Bold.ttf";
      try {
        pdf.addFont(fontPath, "Vazirmatn", "normal");
        pdf.addFont(fontPath, "Vazirmatn", "bold");
      } catch(e) {}
      pdf.setFont("Vazirmatn", "normal");

      const headers = [
        self.$t("Code"),
        self.$t("Name"),
        self.$t("Phone"),
        self.$t("City"),
        "Points"
      ];

      const body = (self.clients || []).map(client => ([
        client.code,
        client.name,
        client.phone,
        client.city,
        client.points
      ]));

      const marginX = 40;
      const rtl =
        (self.$i18n && ['ar','fa','ur','he'].includes(self.$i18n.locale)) ||
        (typeof document !== 'undefined' && document.documentElement.dir === 'rtl');

      autoTable(pdf, {
        head: [headers],
        body: body,
        startY: 110,
        theme: 'striped',
        margin: { left: marginX, right: marginX },
        styles: { font: 'Vazirmatn', fontSize: 9, cellPadding: 4, halign: rtl ? 'right' : 'left', textColor: 33 },
        headStyles: { font: 'Vazirmatn', fontStyle: 'bold', fillColor: [63,81,181], textColor: 255 },
        alternateRowStyles: { fillColor: [245,247,250] },
        didDrawPage: (d) => {
          const pageW = pdf.internal.pageSize.getWidth();
          const pageH = pdf.internal.pageSize.getHeight();

          // Header banner
          pdf.setFillColor(63,81,181);
          pdf.rect(0, 0, pageW, 60, 'F');

          // Title
          pdf.setTextColor(255);
          pdf.setFont('Vazirmatn', 'bold');
          pdf.setFontSize(16);
          const title = self.$t('CustomersList') || 'Customer List';
          rtl ? pdf.text(title, pageW - marginX, 38, { align: 'right' })
              : pdf.text(title, marginX, 38);

          // Reset text color
          pdf.setTextColor(33);

          // Footer page numbers
          pdf.setFontSize(8);
          const pn = `${d.pageNumber} / ${pdf.internal.getNumberOfPages()}`;
          rtl ? pdf.text(pn, marginX, pageH - 14, { align: 'left' })
              : pdf.text(pn, pageW - marginX, pageH - 14, { align: 'right' });
        }
      });

      pdf.save("Customer_List.pdf");
    },

    Get_Clients(page) {
      NProgress.start();
      NProgress.set(0.1);
      axios
        .get(
          "clients?page=" +
            page +
            "&name=" +
            this.Filter_Name +
            "&code=" +
            this.Filter_Code +
            "&phone=" +
            this.Filter_Phone +
            "&email=" +
            this.Filter_Email +
            "&SortField=" +
            this.serverParams.sort.field +
            "&SortType=" +
            this.serverParams.sort.type +
            "&search=" +
            this.search +
            "&limit=" +
            this.limit
        )
        .then(response => {
          this.clients = response.data.clients;
          this.company_info = response.data.company_info;
          this.totalRows = response.data.totalRows;
          this.clients_without_ecommerce = response.data.clients_without_ecommerce;
          this.module_name = response.data.module_name;
          this.accounts = response.data.accounts;
          this.payment_methods = response.data.payment_methods;
          NProgress.done();
          this.isLoading = false;
        })
        .catch(response => {
          NProgress.done();
          setTimeout(() => {
            this.isLoading = false;
          }, 500);
        });
    },

    show_credit_card_details(id){
        NProgress.start();
        NProgress.set(0.1);
        this.customer_id = id;
        this.savedPaymentMethods = [];
        this.selectedCard = null
        this.card_id = '';
         axios.get(`/retrieve-customer?customerId=${id}`)
            .then(response => {
                this.savedPaymentMethods = response.data.data;
                this.card_id = response.data.customer_default_source;
                setTimeout(() => {
                  Fire.$emit("get_credit_card_details");
                }, 500);
            })
            .catch(error => {
               this.savedPaymentMethods = [];
               this.card_id = '';
                setTimeout(() => {
                  Fire.$emit("get_credit_card_details");
                }, 500);
            });
    },

    selectCard(card) {
      this.selectedCard = card;
      this.card_id = card.card_id;
       axios
        .post("update-customer-stripe", {
          customer_id: this.customer_id,
          card_id: this.card_id,
        })
        .then(response => {
          this.makeToast("success", this.$t("Credit_card_changed_successfully"), this.$t("Success"));
        })
        .catch(error => {
            this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
        });
    },

    showDetails(client) {
      NProgress.start();
      NProgress.set(0.1);
      this.client = client;
      Fire.$emit("Get_Details_customers");
    },

    New_Client() {
      this.reset_Form();
      this.editmode = false;
      this.$bvModal.show("New_Customer");
    },

    Edit_Client(client) {
      this.Get_Clients(this.serverParams.page);
      this.reset_Form();
      this.client = client;
      this.editmode = true;
      this.$bvModal.show("New_Customer");
    },

    Create_Client() {
      this.SubmitProcessing = true;
      axios
        .post("clients", {
          name: this.client.name,
          email: this.client.email,
          phone: this.client.phone,
          tax_number: this.client.tax_number,
          country: this.client.country,
          city: this.client.city,
          adresse: this.client.adresse,
          is_royalty_eligible: this.client.is_royalty_eligible
        })
        .then(response => {
          Fire.$emit("Event_Customer");
          this.makeToast("success", this.$t("Successfully_Created"), this.$t("Success"));
          this.SubmitProcessing = false;
        })
        .catch(error => {
          this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
          this.SubmitProcessing = false;
        });
    },

    Update_Client() {
      this.SubmitProcessing = true;
      axios
        .put("clients/" + this.client.id, {
          name: this.client.name,
          email: this.client.email,
          phone: this.client.phone,
          tax_number: this.client.tax_number,
          country: this.client.country,
          city: this.client.city,
          adresse: this.client.adresse,
          is_royalty_eligible: this.client.is_royalty_eligible
        })
        .then(response => {
          Fire.$emit("Event_Customer");
          this.makeToast("success", this.$t("Successfully_Updated"), this.$t("Success"));
          this.SubmitProcessing = false;
        })
        .catch(error => {
          this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
          this.SubmitProcessing = false;
        });
    },

    reset_Form() {
      this.client = {
        id: "",
        name: "",
        email: "",
        phone: "",
        country: "",
        tax_number: "",
        city: "",
        adresse: "",
        is_royalty_eligible: "",
      };
    },

    Remove_Client(id) {
      this.$swal({
        title: this.$t("Delete_Title"),
        text: this.$t("Delete_Text"),
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: this.$t("Delete_cancelButtonText"),
        confirmButtonText: this.$t("Delete_confirmButtonText")
      }).then(result => {
        if (result.value) {
          axios
            .delete("clients/" + id)
            .then(() => {
              this.$swal(
                this.$t("Delete_Deleted"),
                this.$t("Deleted_in_successfully"),
                "success"
              );
              Fire.$emit("Delete_Customer");
            })
            .catch(() => {
              this.$swal(
                this.$t("Delete_Failed"),
                this.$t("Delete.ClientError"),
                "warning"
              );
            });
        }
      });
    },

    delete_by_selected() {
      this.$swal({
        title: this.$t("Delete_Title"),
        text: this.$t("Delete_Text"),
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: this.$t("Delete_cancelButtonText"),
        confirmButtonText: this.$t("Delete_confirmButtonText")
      }).then(result => {
        if (result.value) {
          NProgress.start();
          NProgress.set(0.1);
          axios
            .post("clients/delete/by_selection", {
              selectedIds: this.selectedIds
            })
            .then(() => {
              this.$swal(
                this.$t("Delete_Deleted"),
                this.$t("Deleted_in_successfully"),
                "success"
              );
              Fire.$emit("Delete_Customer");
            })
            .catch(() => {
              setTimeout(() => NProgress.done(), 500);
              this.$swal(
                this.$t("Delete_Failed"),
                this.$t("Delete_Therewassomethingwronge"),
                "warning"
              );
            });
        }
      });
    },

    formatNumber(number, dec) {
      const value = (typeof number === "string"
        ? number
        : number.toString()
      ).split(".");
      if (dec <= 0) return value[0];
      let formated = value[1] || "";
      if (formated.length > dec)
        return `${value[0]}.${formated.substr(0, dec)}`;
      while (formated.length < dec) formated += "0";
      return `${value[0]}.${formated}`;
    },
  },

  created: function() {
    this.Get_Clients(1);

    Fire.$on("get_credit_card_details", () => {
      setTimeout(() => NProgress.done(), 500);
      this.$bvModal.show("show_credit_card_details");
    });

    Fire.$on("Get_Details_customers", () => {
      setTimeout(() => NProgress.done(), 500);
      this.$bvModal.show("showDetails");
    });

    Fire.$on("Event_Customer", () => {
      setTimeout(() => {
        this.Get_Clients(this.serverParams.page);
        this.$bvModal.hide("New_Customer");
      }, 500);
    });

    Fire.$on("Delete_Customer", () => {
      setTimeout(() => {
        this.Get_Clients(this.serverParams.page);
      }, 500);
    });
  }
};
</script>

<style scoped>
/* Material-UI styles are applied globally */
</style>

