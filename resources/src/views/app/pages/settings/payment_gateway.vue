<template>
  <div class="main-content">
    <breadcumb :page="$t('Payment_Gateway')" :folder="$t('Settings')"/>
    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>

    <!-- Payment Gateway -->
    <validation-observer ref="form_payment" v-if="!isLoading">
      <b-form @submit.prevent="Submit_Payment">
        <b-row class="mt-5">
          <b-col lg="12" md="12" sm="12">
            <b-card no-body :header="$t('Payment_Gateway')">
              <b-card-body>
                <b-row>
                  <!-- PayHere Merchant ID -->
                  <b-col lg="6" md="6" sm="12">
                    <b-form-group label="PAYHERE_MERCHANT_ID">
                      <b-form-input
                        type="text"
                        v-model="gateway.payhere_merchant_id"
                        :placeholder="$t('LeaveBlank')"
                      ></b-form-input>
                    </b-form-group>
                  </b-col>

                  <!-- PayHere Merchant Secret -->
                  <b-col lg="6" md="6" sm="12">
                    <b-form-group label="PAYHERE_MERCHANT_SECRET">
                      <b-form-input
                        type="password"
                        v-model="gateway.payhere_merchant_secret"
                        :placeholder="$t('LeaveBlank')"
                      ></b-form-input>
                    </b-form-group>
                  </b-col>

                  <!-- PayHere Mode -->
                  <b-col lg="6" md="6" sm="12">
                    <b-form-group label="PAYHERE_MODE">
                      <b-form-select v-model="gateway.payhere_mode">
                        <option value="sandbox">Sandbox</option>
                        <option value="live">Live</option>
                      </b-form-select>
                    </b-form-group>
                  </b-col>

                  <!-- Remove PayHere Credentials -->
                  <b-col md="6" class="mt-3 mb-3">
                    <label class="switch switch-primary mr-3">
                      {{$t('Remove_PayHere_Credentials')}}
                      <input type="checkbox" v-model="gateway.deleted">
                      <span class="slider"></span>
                    </label>
                  </b-col>

                  <b-col md="12">
                    <b-form-group>
                      <b-button variant="primary" type="submit">
                        <i class="i-Yes me-2 font-weight-bold"></i> {{$t('submit')}}
                      </b-button>
                    </b-form-group>
                  </b-col>
                </b-row>
              </b-card-body>
            </b-card>
          </b-col>
        </b-row>
      </b-form>
    </validation-observer>
  </div>
</template>

<script>
import { mapActions } from "vuex";
import NProgress from "nprogress";

export default {
  metaInfo: {
    title: "Payment Gateway"
  },
  data() {
    return {
      isLoading: true,
      gateway: {
        payhere_merchant_id: "",
        payhere_merchant_secret: "",
        payhere_mode: "sandbox",
        deleted: false,
      },
    };
  },
  methods: {
    ...mapActions(["refreshUserPermissions"]),

    Submit_Payment() {
      this.$refs.form_payment.validate().then(success => {
        if (!success) {
          this.makeToast("danger", this.$t("Please_fill_the_form_correctly"), this.$t("Failed"));
        } else {
          this.Update_Payment();
        }
      });
    },

    makeToast(variant, msg, title) {
      this.$root.$bvToast.toast(msg, {
        title: title,
        variant: variant,
        solid: true
      });
    },

    Update_Payment() {
      NProgress.start();
      NProgress.set(0.1);
      axios.post("payment_gateway", {
        payhere_merchant_id: this.gateway.payhere_merchant_id,
        payhere_merchant_secret: this.gateway.payhere_merchant_secret,
        payhere_mode: this.gateway.payhere_mode,
        deleted: this.gateway.deleted,
      })
      .then(response => {
        this.makeToast("success", this.$t("Successfully_Updated"), this.$t("Success"));
        NProgress.done();
      })
      .catch(error => {
        NProgress.done();
        this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
      });
    },

    Get_Payment_Gateway() {
      axios.get("get_payment_gateway")
      .then(response => {
        this.gateway = response.data.gateway;
        this.isLoading = false;
      })
      .catch(error => {
        this.isLoading = false;
      });
    },
  },

  created: function() {
    this.Get_Payment_Gateway();
  }
};
</script>
