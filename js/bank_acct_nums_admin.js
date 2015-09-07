function BankAcctNumsAdmin() {
    this.init();
}

BankAcctNumsAdmin.prototype = $.extend(BankAcctNums.prototype, {
    /*
     * Initialize method
     */
    init: function() {
        var self = this;

        $('#bank_account_select').change(function() {
            var acctId = $(this).val();
            if (acctId == 0) {
                $('#company_info_sidebar_block').html('');
                self.currentAccount = 0;
            } else if (acctId == 'add') {
                self.showNewAccountBox();
                $("#bank_account_select option:first-child").attr('selected', true);
                $('#company_info_sidebar_block').html('');
                self.currentAccount = 0;
            } else {
                self.getBankAcctInfo(acctId);
                self.currentAccount = acctId;
                self.initFieldsChanges();
            }

            $("#project_select option:first-child").attr('selected', true);
        });
    },

    /**
     * Initialize fields changes
     */
    initFieldsChanges: function() {
        var self = this;
        $('#company_info_sidebar_block .details_page_value').click(function() {
            if (self.editingAccess == 1) {
                $('#new_bank_account h1').text('Edit Bank Account');
                self.resetForm();
                $('#acct_id').val(self.currentAccount);

                var i = 0;
                $('#company_info_sidebar_block .details_page_value').each(function() {
                    var value_block = $(this);
                    var j = 0;
                    $('#new_bank_account_form input[type=text]').each(function() {
                        if (i == j) {
                            $(this).val(value_block.text());
                        }
                        j++;
                    });
                    i++;
                });

                show_modal_box('#new_bank_account', 260, 50);
            } else {
                show_alert("This bank account has already been assigned to a payment and cannot be edited!", 450);
            }
        });
    },

    /**
     * Show new account box
     */
    showNewAccountBox: function() {
        var self = this;
        $.ajax({
            url: "/myaccount/getuserprojectnumber",
            type: "POST",
            async: false,
            success: function(msg) {
               if (msg == 'all') {
                   show_alert("Please select a specific Project for this process.", 500);
               } else {
                   $('#new_bank_account h1').text('New Bank Account');
                   self.resetForm();
                   $('#acct_id').val(0);
                   show_modal_box('#new_bank_account', 260, 50);
               }
            }
        });
    },

    /**
     * Reset form
     */
    resetForm: function() {
        $('#new_bank_account_form input[type=text]').val('');
        $('#new_bank_account_form .errorMessage').hide();
    }
})