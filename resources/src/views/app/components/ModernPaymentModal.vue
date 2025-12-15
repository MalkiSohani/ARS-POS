<template>
  <div class="modern-payment-modal">
    <b-modal
      ref="paymentModal"
      hide-footer
      hide-header
      size="xl"
      id="modern_payment_modal"
      class="payment-modal-wrapper premium-payment-modal-large"
      centered
      @hidden="resetForm"
      body-class="modal-body-custom"
      modal-class="premium-modal"
    >
      <div class="payment-container">
        <!-- Enhanced Header -->
        <div class="payment-header">
          <div class="header-left">
            <div class="icon-wrapper">
              <i class="i-Money-Bag"></i>
          </div>
            <div class="header-text">
              <h2 class="modal-title">{{ isEditMode ? $t('Edit_Payment') : $t('Payment_Checkout') }}</h2>
            </div>
          </div>
          <button type="button" class="close-button" @click="$refs.paymentModal.hide()">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
        </div>

        <!-- Content Area -->
        <div class="payment-content">
          <!-- Left Side: Transaction Info -->
          <div class="transaction-info">
            <!-- Amount Card -->
            <div class="amount-card">
              <div class="amount-card-header">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="10"></circle>
                  <path d="M12 6v6l4 2"></path>
                </svg>
                <span>{{$t('Transaction_Summary')}}</span>
            </div>
              <div class="amount-display">
                <span class="currency-label">{{$t('Total_Amount')}}</span>
                <span class="amount-large">{{ formatCurrency(paymentForm.amountDue) }}</span>
            </div>
        </div>

            <!-- Payment Status -->
            <div class="payment-status-card">
              <h3 class="card-title">{{$t('Payment_Breakdown')}}</h3>
              <div class="status-grid">
                <div class="status-box">
                  <div class="status-icon paying">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                      <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                  </div>
                  <div class="status-details">
                    <span class="status-name">{{$t('Paying')}}</span>
                    <span class="status-amount">{{ formatCurrency(totalPaid) }}</span>
                  </div>
                </div>
                <div class="status-box">
                  <div class="status-icon balance">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                      <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                    </svg>
                  </div>
                  <div class="status-details">
                    <span class="status-name">{{$t('Balance')}}</span>
                    <span class="status-amount balance-text">{{ formatCurrency(balance) }}</span>
                  </div>
                </div>
                <div class="status-box">
                  <div class="status-icon change">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                      <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                  </div>
                  <div class="status-details">
                    <span class="status-name">{{$t('Change')}}</span>
                    <span class="status-amount change-text">{{ formatCurrency(changeReturn) }}</span>
                  </div>
                </div>
              </div>
            </div>

          </div>

          <!-- Right Side: Payment Form -->
          <div class="payment-form-area">
            <form @submit.prevent="submitPayment" class="enhanced-form">
              <!-- Multi Payment Lines with Right Vertical Quick Amounts -->
              <div class="form-group">
                <div class="payment-lines-layout">
                  <!-- Left: Lines -->
                  <div class="payment-lines-list">
                    <div v-for="(p, idx) in paymentLines" :key="idx" class="payment-line-card">
                      <div class="payment-line-header">
                        <div class="line-badge">{{ idx + 1 }}</div>
                        <span class="line-title">{{$t('Payment')}} #{{ idx + 1 }}</span>
                        <button v-if="paymentLines.length > 1" type="button" class="line-remove-btn" @click="removePaymentLine(idx)">
                          <i class="i-Trash"></i>
                        </button>
                      </div>
                      <div class="payment-line-body">
                        <div class="input-field">
                          <label class="field-label">{{$t('Amount')}}</label>
                          <div class="input-with-icon">
                            <span class="input-icon">{{ currency }}</span>
                            <input v-model.number="p.amount" type="number" step="0.01" min="0" placeholder="0.00" class="form-input" />
                          </div>
                        </div>
                        <div class="input-field">
                          <label class="field-label">{{$t('Account')}}</label>
                          <select v-model="p.accountId" class="form-select">
                            <option value="">{{$t('Select_Account')}}</option>
                            <option v-for="a in resolvedAccounts" :key="a.id" :value="a.id">{{ a.account_name || a.name }}</option>
                          </select>
                        </div>
                      <div class="form-group">
                        <div class="payment-methods">
                          <div
                            v-for="m in resolvedPaymentMethods"
                            :key="m.id"
                            class="method-card"
                            :class="{ selected: String(p.paymentMethodId) === String(m.id) }"
                            @click="changePaymentMethod(p, m)"
                          >
                            <div class="method-content">
                              <div class="method-icon-wrapper">
                                <i :class="m.icon"></i>
                              </div>
                              <span class="method-label">{{ m.name }}</span>
                            </div>
                            <div v-if="String(p.paymentMethodId) === String(m.id)" class="selected-indicator">
                              <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                              </svg>
                            </div>
                          </div>
                        </div>
                      </div>
                      </div>
                    </div>
                    <button type="button" class="action-btn cancel-btn add-line-btn" @click="addPaymentLine">
                      <i class="i-Plus"></i> {{$t('Add_Payment_Method')}}
                    </button>
                  </div>

                  <!-- Right: Vertical Quick Amounts -->
                  <div class="quick-amount-rail">
                    <div class="quick-amount-vertical">
                      <button
                        type="button"
                        class="quick-btn"
                        v-for="amt in quickAmountOptions"
                        :key="amt"
                        @click="setQuickAmount(amt)"
                      >
                        {{ formatCurrency(amt) }}
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Payment Date Only -->
              <div class="form-group">
                <div class="input-field">
                  <label class="field-label">{{$t('Payment_Date')}} *</label>
                  <input v-model="paymentForm.date" type="date" class="form-select" />
                </div>
              </div>

              <!-- Notes -->
              <div class="form-group">
                <div class="dual-input">
                  <div class="input-field">
                    <label class="field-label">{{$t('Payment_note')}}</label>
                    <textarea v-model="paymentNote" rows="3" class="form-textarea" :placeholder="$t('Add_Payment_Note')"></textarea>
                  </div>
                  <div class="input-field">
                    <label class="field-label">{{$t('Sale_note')}}</label>
                    <textarea v-model="saleNote" rows="3" class="form-textarea" :placeholder="$t('Add_Sale_Note')"></textarea>
                  </div>
                </div>
              </div>

              <!-- Email/SMS -->
              <div class="form-group">
                <div class="checkboxes-group">
                  <label class="checkbox-item">
                    <input type="checkbox" v-model="sendEmail" />
                    {{$t('Send_Email_Receipt')}}
                  </label>
                  <label class="checkbox-item">
                    <input type="checkbox" v-model="sendSMS" />
                    {{$t('Send_SMS_Receipt')}}
                  </label>
                </div>
              </div>

        </form>
          </div>
        </div>
      </div>
      <!-- Footer Action Bar -->
      <div class="payment-footer">
        <button
          type="button"
          class="footer-btn footer-cancel"
          @click="$refs.paymentModal.hide()"
        >
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
          {{$t('Cancel')}}
        </button>
        <button
          type="button"
          class="footer-btn footer-submit"
          :disabled="isSubmitting"
          @click="submitPayment"
        >
          <span v-if="!isSubmitting" class="btn-content">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
              <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
            </svg>
            {{ isEditMode ? $t('Update_Payment') : $t('Complete_Payment') }}
          </span>
          <span v-else class="btn-content">
            <span class="loading-spinner"></span>
            {{$t('Processing')}}...
          </span>
        </button>
      </div>
    </b-modal>

    <!-- PayHere Payment Form (Hidden) -->
    <form ref="payhereForm" method="post" style="display:none;">
      <input type="hidden" name="merchant_id" v-model="payhereData.merchant_id">
      <input type="hidden" name="return_url" v-model="payhereData.return_url">
      <input type="hidden" name="cancel_url" v-model="payhereData.cancel_url">
      <input type="hidden" name="notify_url" v-model="payhereData.notify_url">
      <input type="hidden" name="order_id" v-model="payhereData.order_id">
      <input type="hidden" name="items" v-model="payhereData.items">
      <input type="hidden" name="currency" v-model="payhereData.currency">
      <input type="hidden" name="amount" v-model="payhereData.amount">
      <input type="hidden" name="first_name" v-model="payhereData.first_name">
      <input type="hidden" name="last_name" v-model="payhereData.last_name">
      <input type="hidden" name="email" v-model="payhereData.email">
      <input type="hidden" name="phone" v-model="payhereData.phone">
      <input type="hidden" name="address" v-model="payhereData.address">
      <input type="hidden" name="city" v-model="payhereData.city">
      <input type="hidden" name="country" v-model="payhereData.country">
      <input type="hidden" name="hash" v-model="payhereData.hash">
      <input type="hidden" name="custom_1" v-model="payhereData.custom_1">
      <input type="hidden" name="custom_2" v-model="payhereData.custom_2">
    </form>
  </div>
</template>

<script>
export default {
  name: 'ModernPaymentModal',
  props: {
    paymentMethods: { type: Array, default: () => [] },
    accounts: { type: Array, default: () => [] },
    currency: { type: String, default: 'Rs.' },
    clientId: { type: [Number, String], default: null },
    warehouseId: { type: [Number, String], default: null },
    sale: { type: Object, default: () => ({}) },
    details: { type: Array, default: () => [] },
    grandTotal: { type: Number, default: 0 },
    discountFromPoints: { type: Number, default: 0 },
    usedPoints: { type: Number, default: 0 },
    createPosUrl: { type: String, default: 'pos/create_pos' },
    draftSaleId: { type: [Number, String], default: null }
  },
  data() {
    return {
      isEditMode: false,
      isSubmitting: false,
      paymentProcessing: false,
      paymentLines: [],
      sendEmail: false,
      sendSMS: false,
      paymentNote: '',
      saleNote: '',
      payhereData: {
        merchant_id: '',
        return_url: '',
        cancel_url: '',
        notify_url: '',
        order_id: '',
        items: '',
        currency: '',
        amount: '',
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        address: '',
        city: '',
        country: '',
        hash: '',
        custom_1: '',
        custom_2: ''
      },
      paymentForm: {
        amountDue: 0,
        accountId: '',
        date: new Date().toISOString().split('T')[0],
        reference: '',
        notes: ''
      }
    };
  },
  computed: {
    totalPaid() {
      return (this.paymentLines || []).reduce((sum, line) => sum + (Number(line.amount) || 0), 0);
    },
    balance() {
      const amountDue = this.paymentForm.amountDue || 0;
      const paid = this.totalPaid;
      return Math.max(0, amountDue - paid);
    },
    changeReturn() {
      const over = this.totalPaid - (this.paymentForm.amountDue || 0);
      return over > 0 ? over : 0;
    },
    resolvedPaymentMethods() {
      const methods = (this.paymentMethods || []).map(m => ({
        ...m,
        icon: m.icon || this.getPaymentIcon(m)
      }));
      const isCash = (m) => !!(m && m.name && m.name.toLowerCase().includes('cash'));
      const cash = methods.filter(isCash);
      const others = methods.filter(m => !isCash(m));
      return cash.concat(others);
    },
    resolvedAccounts() {
      return this.accounts || [];
    },
    quickAmountOptions() {
      const base = [this.paymentForm && this.paymentForm.amountDue, 50, 100, 500, 1000, 5000]
        .map(v => Number(v))
        .filter(v => Number.isFinite(v));
      const seen = new Set();
      const unique = [];
      for (const v of base) {
        const key = v.toFixed(2);
        if (!seen.has(key)) {
          seen.add(key);
          unique.push(parseFloat(key));
        }
      }
      return unique;
    }
  },
  methods: {
    makeToast(variant, msg, title) {
      if (this.$root && this.$root.$bvToast) {
        this.$root.$bvToast.toast(msg, {
          title: title,
          variant: variant,
          solid: true
        });
      }
    },
    getPaymentIcon(method) {
      const name = ((method && method.name) || '').toLowerCase();
      if (name.includes('cash')) return 'i-Money-Bag';
      if (name.includes('card') || name.includes('credit')) return 'i-Credit-Card';
      if (name.includes('bank') || name.includes('transfer')) return 'i-Bank';
      if (name.includes('cheque') || name.includes('check')) return 'i-File';
      return 'i-Wallet';
    },
    getCashMethodId() {
      const list = this.resolvedPaymentMethods || [];
      const found = list.find(m => m && m.name && m.name.toLowerCase().includes('cash'));
      return found ? found.id : null;
    },
    changePaymentMethod(line, method) {
      line.paymentMethodId = method.id;
    },
    formatCurrency(value) {
      if (!value && value !== 0) return 'Rs.0.00';
      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'LKR'
      }).format(value);
    },
    addPaymentLine() {
      const defaultMethodId = this.getCashMethodId();
      this.paymentLines.push({ amount: 0, paymentMethodId: defaultMethodId, accountId: this.paymentForm.accountId || '' });
    },
    removePaymentLine(index) {
      this.paymentLines.splice(index, 1);
    },
    setQuickAmount(amount) {
      if (!this.paymentLines.length) this.addPaymentLine();
      this.paymentLines[0].amount = Number(amount) || 0;
    },

    async submitPayment() {
      await this.CreatePOS();
    },

    async CreatePOS() {
      if (typeof NProgress !== 'undefined') {
        NProgress.start();
        NProgress.set(0.1);
      }

      const zeroTotal = this.totalPaid <= 0;
      if (!this.paymentLines.length || (zeroTotal && this.paymentLines.length > 1)) {
        if (typeof NProgress !== 'undefined') NProgress.done();
        this.makeToast('danger', this.$t ? this.$t('InvalidData') : 'Invalid data', this.$t ? this.$t('Failed') : 'Failed');
        return;
      }

      if (Array.isArray(this.resolvedAccounts) && this.resolvedAccounts.length > 0) {
        const allLinesCovered = (this.paymentLines || []).every(l => !!l.accountId);
        if (!allLinesCovered) {
          if (typeof NProgress !== 'undefined') NProgress.done();
          this.makeToast('warning', this.$t ? this.$t('Please_select_Account') : 'Please select Account', this.$t ? this.$t('Warning') : 'Warning');
          return;
        }
      }

      const total = parseFloat(this.totalPaid);
      const due = parseFloat((this.paymentForm.amountDue || this.grandTotal || 0).toFixed(2));
      const multi = this.paymentLines.length > 1;
      if (multi && total > due) {
        this.makeToast('warning', this.$t ? this.$t('TotalPaidExceedsGrandTotalForMultiPayment') : 'Total paid exceeds grand total', this.$t ? this.$t('Warning') : 'Warning');
        if (typeof NProgress !== 'undefined') NProgress.done();
        return;
      }

      this.paymentProcessing = true;
      this.isSubmitting = true;

      try {
        const payload = {
          client_id: this.clientId,
          warehouse_id: this.warehouseId,
          tax_rate: this.sale && this.sale.tax_rate ? this.sale.tax_rate : 0,
          TaxNet: this.sale && this.sale.TaxNet ? this.sale.TaxNet : 0,
          discount: this.sale && this.sale.discount ? this.sale.discount : 0,
          shipping: this.sale && this.sale.shipping ? this.sale.shipping : 0,
          notes: this.saleNote || (this.sale && this.sale.notes) || '',
          details: this.details,
          GrandTotal: this.grandTotal || this.paymentForm.amountDue || total,
          payments: (this.paymentLines || []).map((l) => ({
            amount: Number(l.amount) || 0,
            payment_method_id: l.paymentMethodId,
            account_id: l.accountId || null
          })),
          send_email: this.sendEmail,
          send_sms: this.sendSMS,
          payment_note: this.paymentNote || '',
          discount_from_points: this.discountFromPoints || 0,
          used_points: this.usedPoints || 0,
          draft_sale_id: this.draftSaleId || null
        };

        const response = await axios.post(this.createPosUrl, payload);

        if (response && response.data && response.data.success === true) {
          if (typeof NProgress !== 'undefined') NProgress.done();
          this.paymentProcessing = false;
          this.isSubmitting = false;

          // Check if PayHere payment is required
          if (response.data.requires_payhere && response.data.payhere_data) {
            // Hide modal first
            this.$refs.paymentModal && this.$refs.paymentModal.hide && this.$refs.paymentModal.hide();

            // Populate PayHere form and submit
            this.payhereData = response.data.payhere_data;

            this.$nextTick(() => {
              const form = this.$refs.payhereForm;
              if (form) {
                form.action = response.data.payhere_data.action_url;
                form.submit();
              }
            });
          } else {
            // Regular payment completed - show invoice
            if (this.$parent && typeof this.$parent.Invoice_POS === 'function') {
              await this.$parent.Invoice_POS(response.data.id);
            }
            this.$emit('payment-success', { id: response.data.id, payload });
            this.$refs.paymentModal && this.$refs.paymentModal.hide && this.$refs.paymentModal.hide();
          }
        } else {
          if (typeof NProgress !== 'undefined') NProgress.done();
          this.paymentProcessing = false;
          this.isSubmitting = false;
          this.makeToast('danger', this.$t ? this.$t('InvalidData') : 'Invalid data', this.$t ? this.$t('Failed') : 'Failed');
        }
      } catch (error) {
        if (typeof NProgress !== 'undefined') NProgress.done();
        this.paymentProcessing = false;
        this.isSubmitting = false;
        this.makeToast('danger', this.$t ? this.$t('InvalidData') : 'Invalid data', this.$t ? this.$t('Failed') : 'Failed');
      }
    },

    resetForm() {
      this.paymentForm = {
        amountDue: this.grandTotal || 0,
        accountId: '',
        date: new Date().toISOString().split('T')[0],
        reference: '',
        notes: ''
      };
      this.paymentLines = [];
      this.sendEmail = false;
      this.sendSMS = false;
      this.paymentNote = '';
      this.saleNote = '';
      this.isSubmitting = false;
    },

    openModal(data = {}) {
      if (data.id) {
        this.isEditMode = true;
        Object.assign(this.paymentForm, data);
      } else {
        this.isEditMode = false;
        if (data.reference) {
          this.paymentForm.reference = data.reference;
        }
        if (data.notes) {
          this.paymentForm.notes = data.notes;
        }
      }

      if (Object.prototype.hasOwnProperty.call(data, 'amountDue') && data.amountDue !== undefined && data.amountDue !== null) {
        this.paymentForm.amountDue = Number(data.amountDue) || 0;
      } else {
        this.paymentForm.amountDue = this.grandTotal || 0;
      }

      this.paymentLines = [];
      const defaultMethodId = this.getCashMethodId();
      this.paymentLines.push({ amount: Number(this.paymentForm.amountDue || 0), paymentMethodId: defaultMethodId, accountId: this.paymentForm.accountId || '' });
      this.paymentNote = '';
      this.saleNote = '';
      this.sendEmail = false;
      this.sendSMS = false;
      this.$refs.paymentModal.show();
    }
  }
};
</script>

<style lang="scss">
/* ========================================
   PREMIUM PAYMENT MODAL DESIGN
   ======================================== */

.modern-payment-modal {
  .modal-dialog {
    max-width: 1100px !important;
    width: 90vw !important;
  }

  .modal-content {
    border: none !important;
    border-radius: 18px !important;
    box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15) !important;
    overflow: hidden !important;
  }

  .modal-body-custom {
    padding: 0 !important;
  }

  &.premium-payment-modal-large {
    .modal-dialog {
      max-width: 1100px !important;
    }
  }
}

/* Global modal override for this specific modal */
#modern_payment_modal {
  .modal-dialog {
    max-width: 1100px !important;
    width: 90vw !important;
  }

  .modal-content {
    border: none !important;
    border-radius: 18px !important;
    box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15) !important;
    overflow: hidden !important;
  }

  .modal-body {
    padding: 0 !important;
  }
}

/* ========================================
   PAYMENT CONTAINER
   ======================================== */

.payment-container {
  background: #ffffff;
  min-height: 450px;
  max-height: 85vh;
  display: flex;
  flex-direction: column;
}

/* ========================================
   HEADER SECTION
   ======================================== */

.payment-header {
  background: linear-gradient(135deg, #0963AC 0%, #09B3E9 100%);
  padding: 14px 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: relative;
  overflow: hidden;

  &::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
    border-radius: 50%;
  }

  .header-left {
        display: flex;
        align-items: center;
    gap: 14px;
    z-index: 1;
  }

  .icon-wrapper {
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: white;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
  }

  .header-text {
    .modal-title {
      margin: 0;
      font-size: 18px;
      font-weight: 700;
      color: white;
      letter-spacing: -0.5px;
    }
  }

  .close-button {
    width: 36px;
    height: 36px;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255, 255, 255, 0.2);
    border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
    cursor: pointer;
      transition: all 0.3s ease;
    z-index: 1;
    color: white;

      &:hover {
      background: rgba(255, 255, 255, 0.25);
        transform: rotate(90deg);
      }
    }
  }

/* ========================================
   CONTENT AREA
   ======================================== */

.payment-content {
  display: grid;
  grid-template-columns: 300px 1fr;
  min-height: 400px;
  flex: 1;
  overflow: hidden;

  @media (max-width: 992px) {
    display: block;
    grid-template-columns: none;
    overflow-y: auto;
  }
}

/* ========================================
   TRANSACTION INFO SIDEBAR
   ======================================== */

.transaction-info {
  background: linear-gradient(180deg, #f8f9fc 0%, #eef2f7 100%);
  padding: 14px 12px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  border-right: 1px solid rgba(0, 0, 0, 0.06);
  overflow-y: auto;
  overflow-x: hidden;

  /* Custom scrollbar */
  &::-webkit-scrollbar {
    width: 6px;
  }

  &::-webkit-scrollbar-track {
    background: transparent;
  }

  &::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 3px;

    &:hover {
      background: #9ca3af;
    }
  }
}

.amount-card {
  background: white;
  border-radius: 12px;
  padding: 12px;
  box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
  border: 1px solid rgba(0, 0, 0, 0.04);

  .amount-card-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
    color: #0963AC;
    font-weight: 600;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;

    svg {
      flex-shrink: 0;
      width: 16px;
      height: 16px;
    }
  }

  .amount-display {
    text-align: center;
    padding: 12px 0;
    border-radius: 10px;
    background: linear-gradient(135deg, rgba(9, 99, 172, 0.05) 0%, rgba(9, 179, 233, 0.05) 100%);

    .currency-label {
      display: block;
      font-size: 10px;
      color: #6b7280;
      font-weight: 500;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 4px;
    }

    .amount-large {
      display: block;
      font-size: 26px;
      font-weight: 800;
      background: linear-gradient(135deg, #0963AC 0%, #09B3E9 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
  }

  .transaction-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 14px;
    padding-top: 14px;
    border-top: 1px solid rgba(0, 0, 0, 0.06);

    .meta-item {
      display: flex;
      flex-direction: column;
      gap: 4px;

      .meta-label {
        font-size: 9px;
        color: #9ca3af;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
      }

      .meta-value {
        font-size: 12px;
        font-weight: 700;
        color: #1f2937;
      }
    }

    .meta-divider {
      width: 1px;
      height: 24px;
      background: rgba(0, 0, 0, 0.06);
    }

    .status-badge {
      display: flex;
      align-items: center;
      gap: 4px;
      background: linear-gradient(135deg, #0963AC 0%, #09B3E9 100%);
      color: white;
      padding: 4px 10px;
      border-radius: 16px;
      font-size: 10px;
      font-weight: 600;

      .status-dot {
        width: 5px;
        height: 5px;
        background: white;
        border-radius: 50%;
        animation: pulse 2s ease infinite;
      }
    }
  }
}

.payment-status-card {
  background: white;
  border-radius: 12px;
  padding: 12px;
  box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
  border: 1px solid rgba(0, 0, 0, 0.04);

  .card-title {
    font-size: 11px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 10px 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .status-grid {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .status-box {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    background: #f9fafb;
    border-radius: 10px;
    transition: all 0.3s ease;

    &:hover {
      background: #f3f4f6;
      transform: translateX(4px);
    }

    .status-icon {
      width: 30px;
      height: 30px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;

      &.paying {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
      }

      &.balance {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
      }

      &.change {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
      }

      svg {
        width: 14px;
        height: 14px;
      }
    }

    .status-details {
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 2px;

      .status-name {
        font-size: 9px;
        color: #6b7280;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
      }

      .status-amount {
        font-size: 14px;
        font-weight: 700;
        color: #1f2937;

        &.balance-text {
          color: #f59e0b;
        }

        &.change-text {
          color: #3b82f6;
        }
      }
    }
  }
}

/* ========================================
   PAYMENT FORM AREA
   ======================================== */

.payment-form-area {
  padding: 16px 20px;
  overflow-y: auto;
  overflow-x: hidden;
  max-height: calc(85vh - 120px);
  position: relative;

  /* Custom scrollbar */
  &::-webkit-scrollbar {
    width: 6px;
  }

  &::-webkit-scrollbar-track {
    background: #f3f4f6;
    border-radius: 4px;
  }

  &::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 4px;

    &:hover {
      background: #9ca3af;
    }
  }

  @media (max-width: 992px) {
    max-height: none;
    padding: 16px 16px;
  }
}

.enhanced-form {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-label {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 11px;
  font-weight: 700;
  color: #1f2937;
  text-transform: uppercase;
  letter-spacing: 0.5px;

  svg {
    color: #667eea;
    flex-shrink: 0;
    width: 13px;
    height: 13px;
  }
}

.field-label {
  font-size: 11px;
  font-weight: 600;
  color: #4b5563;
  margin-bottom: 4px;
}

/* Payment Methods */
.payment-methods {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 8px;

  @media (max-width: 768px) {
    grid-template-columns: repeat(2, 1fr);
  }
}

.method-card {
  position: relative;
  padding: 10px 8px;
  background: white;
  border: 2px solid #e5e7eb;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  min-height: 70px;

  &:hover {
    border-color: #0963AC;
    background: linear-gradient(135deg, rgba(9, 99, 172, 0.03) 0%, rgba(9, 179, 233, 0.03) 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(9, 99, 172, 0.12);
  }

  &.selected {
    border-color: #0963AC;
    background: linear-gradient(135deg, rgba(9, 99, 172, 0.08) 0%, rgba(9, 179, 233, 0.08) 100%);
    box-shadow: 0 6px 16px rgba(9, 99, 172, 0.15);
  }

  .method-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    width: 100%;
  }

  .method-icon-wrapper {
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, #0963AC 0%, #09B3E9 100%);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    color: white;
    box-shadow: 0 2px 8px rgba(9, 99, 172, 0.25);
  }

  .method-label {
    font-size: 11px;
    font-weight: 600;
    color: #1f2937;
  }

  .selected-indicator {
    position: absolute;
    top: 4px;
    right: 4px;
    width: 18px;
    height: 18px;
    background: #10b981;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    box-shadow: 0 2px 6px rgba(16, 185, 129, 0.3);

    svg {
      width: 10px;
      height: 10px;
    }
  }
}

/* Amount Inputs */
.amount-row {
      display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;

  @media (max-width: 768px) {
    grid-template-columns: 1fr;
  }
}

.input-field {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.input-with-icon {
      position: relative;
      display: flex;
      align-items: center;

  .input-icon {
        position: absolute;
    left: 16px;
    font-size: 16px;
    font-weight: 700;
        color: #0963AC;
    pointer-events: none;
  }

  .form-input {
    width: 100%;
    padding: 10px 12px 10px 32px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    color: #1f2937;
    transition: all 0.3s ease;
    background: white;

    &:focus {
      outline: none;
      border-color: #0963AC;
      box-shadow: 0 0 0 3px rgba(9, 99, 172, 0.1);
    }

    &::placeholder {
      color: #9ca3af;
      font-weight: 400;
    }
  }
}

/* Change Notification */
.change-notification {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 14px;
  background: linear-gradient(135deg, #10b98110 0%, #05966910 100%);
  border: 2px solid #10b981;
  border-radius: 8px;
  margin-top: 8px;
  animation: slideIn 0.4s ease;

  .change-icon {
    width: 30px;
    height: 30px;
    background: #10b981;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-shrink: 0;

    svg {
      width: 16px;
      height: 16px;
    }
  }

  .change-content {
    flex: 1;
    display: flex;
    justify-content: space-between;
    align-items: center;

    .change-title {
      font-size: 10px;
      font-weight: 600;
      color: #047857;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .change-amount {
      font-size: 16px;
      font-weight: 800;
      color: #10b981;
    }
  }
}

/* Dual Input */
.dual-input {
      display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;

  @media (max-width: 768px) {
    grid-template-columns: 1fr;
  }
}

.form-select {
  width: 100%;
  padding: 10px 12px;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 500;
  color: #1f2937;
  transition: all 0.3s ease;
  background: white;
  cursor: pointer;

  &:focus {
    outline: none;
    border-color: #0963AC;
    box-shadow: 0 0 0 3px rgba(9, 99, 172, 0.1);
  }
}

.form-textarea {
  width: 100%;
  padding: 10px 12px;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 500;
  color: #1f2937;
  transition: all 0.3s ease;
  background: white;
  resize: vertical;
  min-height: 60px;
  font-family: inherit;

  &:focus {
    outline: none;
    border-color: #0963AC;
    box-shadow: 0 0 0 3px rgba(9, 99, 172, 0.1);
  }

  &::placeholder {
    color: #9ca3af;
  }
}

/* Payment lines layout: left list + right vertical quick amounts */
.payment-lines-layout {
  display: grid;
  grid-template-columns: 1fr 180px;
  gap: 12px;

  @media (max-width: 992px) {
    grid-template-columns: 1fr;
  }
}

.payment-lines-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.payment-line-card {
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  overflow: hidden;
}

.payment-line-header {
  background: white;
  border-bottom: 1px solid #e5e7eb;
  padding: 10px 12px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.line-badge {
  background: #0963AC;
  color: white;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 11px;
  font-weight: 700;
}

.line-title {
  flex: 1;
  font-size: 12px;
  font-weight: 700;
  color: #1f2937;
}

.line-remove-btn {
  color: #ef4444;
  padding: 0;
  background: none;
  border: none;
  cursor: pointer;
  transition: all .2s ease;
  font-size: 18px;

  &:hover {
    color: #dc2626;
    transform: scale(1.1);
  }
}

.payment-line-body {
  padding: 12px;
  display: grid;
  grid-template-columns: 1fr;
  gap: 10px;

  @media (max-width: 768px) {
    grid-template-columns: 1fr;
  }
}

.method-pills {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.method-pill {
  padding: 8px 10px;
  border: 2px solid #e5e7eb;
  background: #fff;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all .2s ease;

  &:hover {
    border-color: #0963AC;
    background: rgba(9, 99, 172, 0.06);
  }

  &.selected {
    border-color: #0963AC;
    background: rgba(9, 99, 172, 0.12);
    color: #2b2e83;
  }
}

.add-line-btn {
  margin-top: 8px;
  outline: none !important;
  box-shadow: none !important;
  &:focus,
  &:active,
  &:focus-visible {
    outline: none !important;
    box-shadow: none !important;
  }
}

.quick-amount-rail {
  display: flex;
  flex-direction: column;
  align-items: stretch;
  gap: 8px;
}

.quick-amount-title {
  font-size: 11px;
  font-weight: 700;
  color: #1f2937;
  text-transform: uppercase;
  letter-spacing: .5px;
}

.quick-amount-vertical {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.quick-btn {
  outline: none !important;
  box-shadow: none !important;
  &:focus,
  &:active,
  &:focus-visible {
    outline: none !important;
    box-shadow: none !important;
  }
}

/* Action Buttons */
.form-actions-new {
  position: sticky;
  bottom: 0;
  display: flex;
  gap: 10px;
  padding: 12px 20px;
  margin: 16px -20px -16px -20px;
  background: white;
  border-top: 2px solid #e5e7eb;
  box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.08);
  z-index: 10;

  @media (max-width: 768px) {
    flex-direction: row;
    gap: 8px;
    padding: 10px 16px;
    margin: 16px -16px -16px -16px;
  }
}

.action-btn {
  flex: 1;
  padding: 12px 20px;
  border: none;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  min-height: 42px;

  &:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }

  .btn-content {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  svg {
    width: 16px;
    height: 16px;
  }
}

.cancel-btn {
  background: #f3f4f6;
  color: #4b5563;
  border: 2px solid #e5e7eb;

  &:hover:not(:disabled) {
    background: #e5e7eb;
    border-color: #d1d5db;
    transform: none;
  }
}

.submit-btn {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);

  &:hover:not(:disabled) {
    box-shadow: 0 6px 16px rgba(16, 185, 129, 0.35);
    transform: none;
  }

  &:active:not(:disabled) {
    transform: scale(0.98);
  }
}

/* Fixed footer buttons */
.payment-footer {
  position: sticky;
  bottom: 0;
  display: flex;
  gap: 10px;
  padding: 12px 20px;
  background: white;
  border-top: 2px solid #e5e7eb;
  box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.08);
  z-index: 20;
}

/* Saved Cards minimal styling */
.saved-cards {
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  overflow: hidden;
}

.saved-cards-header {
  padding: 10px 12px;
  font-size: 11px;
  font-weight: 700;
  color: #1f2937;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  background: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
}

.saved-cards-table {
  margin: 0;
  width: 100%;
}

.saved-cards-table thead th {
  font-size: 11px;
  font-weight: 700;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  background: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
}

.saved-cards-table tbody td {
  vertical-align: middle;
  font-size: 12px;
}

.bg-selected-card {
  background: linear-gradient(135deg, rgba(9, 99, 172, 0.06) 0%, rgba(9, 179, 233, 0.06) 100%);
}

.default-badge {
  display: inline-block;
  margin-left: 8px;
  padding: 2px 6px;
  border-radius: 999px;
  background: #ecfeff;
  color: #0369a1;
  border: 1px solid #67e8f9;
  font-size: 10px;
  font-weight: 700;
  vertical-align: middle;
}

.selected-badge {
  display: inline-block;
  margin-left: 6px;
  padding: 2px 6px;
  border-radius: 999px;
  background: #e8fff3;
  color: #065f46;
  border: 1px solid #6ee7b7;
  font-size: 10px;
  font-weight: 700;
  vertical-align: middle;
}

.footer-btn {
  flex: 1;
  padding: 12px 20px;
  border: none;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  min-height: 42px;
}

.footer-cancel {
  background: #f3f4f6;
  color: #4b5563;
  border: 2px solid #e5e7eb;
}

.footer-submit {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
}

/* Loading Spinner */
.loading-spinner {
    display: inline-block;
  width: 16px;
  height: 16px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
  }

/* Animations */
  @keyframes spin {
    to {
      transform: rotate(360deg);
    }
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Responsive Design */
@media (max-width: 992px) {
  .payment-header {
    padding: 14px 20px;

    .icon-wrapper {
      width: 40px;
      height: 40px;
      font-size: 20px;
    }

    .header-text {
      .modal-title {
        font-size: 18px;
      }
    }
  }

  .transaction-info {
    padding: 14px 12px;
  }

  .payment-form-area {
    padding: 16px 20px;
  }
}

@media (max-width: 768px) {
  .payment-header {
    padding: 12px 16px;

    .header-left {
      gap: 10px;
    }

    .icon-wrapper {
      width: 36px;
      height: 36px;
      font-size: 18px;
    }

    .header-text {
      .modal-title {
        font-size: 16px;
      }

      .modal-subtitle {
        font-size: 10px;
      }
    }

    .close-button {
      width: 32px;
      height: 32px;
    }
  }

  .payment-form-area {
    padding: 14px 16px;
  }

  /* Ensure transaction info doesn't scroll vertically on small screens */
  .transaction-info {
    overflow-y: visible;
    max-height: none;
  }
}
</style>

