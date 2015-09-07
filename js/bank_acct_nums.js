function BankAcctNums() {
    this.init();
}

BankAcctNums.prototype = {
    /**
     * Cancel editing of bank account if any Payment is linking to it
     */
    editingAccess: 0,

    /**
     * Current account
     */
    currentAccount: 0,

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
                $('#company_info_sidebar_block').html('');
                self.currentAccount = 0;
            } else {
                self.getBankAcctInfo(acctId);
                self.currentAccount = acctId;
            }

            $("#project_select option:first-child").attr('selected', true);
        });
    },

    /**
     * Get bank account info
     * @param int acctId
     */
    getBankAcctInfo: function(acctId) {
        var self = this;

        $.ajax({
            url: "/myaccount/getbankacctinfo",
            data: {acctId: acctId},
            type: "POST",
            dataType: 'json',
            async: false,
            success: function(data) {
                $('#company_info_sidebar_block').html(data.info).show();
                self.editingAccess = data.editingAccess;
            }
        });
    }
}