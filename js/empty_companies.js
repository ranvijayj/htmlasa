function EmptyCompanies() {
    this.init();
    this.initCompaniesGrid();
}

EmptyCompanies.prototype = {
    /**
     * Click timeout
     */
    timeoutClick: false,

    /**
     * Company Id
     */
    companyID: false,

    /**
     * Initialize method
     */
    init: function () {
        var self = this;

        $('#company_name_input_empty_companies').keydown(function() {
            clearTimeout(self.timeoutClick);
            self.timeoutClick = setTimeout(function() {
                self.updateCompaniesList();
            }, 800);
        });
    },

    /**
     * Initialize Companies grid
     */
    initCompaniesGrid: function() {
        var self = this;

        $('#empty_companies_grid tbody tr').click(function() {
            var companyId = $(this).attr('id');
            companyId = companyId.slice(13);
            if (self.companyID != companyId) {
                $('#empty_companies_grid tbody tr').css('background', 'none');
                $(this).css('background-color', '#dFdDdD');
                self.companyID = companyId;
                self.getCompanyInfo(companyId);
            }
        });

        $('#empty_companies_grid tbody tr a').click(function() {
            var statusCell = $(this).parent().parent().find('.status');
            statusCell.text("Y");
        });
    },

    /**
     * Updates Companies list
     */
    updateCompaniesList: function () {
        var self = this;
        var companyName = $('#company_name_input_empty_companies').val();
        $('#empty_companies > div').scrollTop(0).prepend("<div class='loadinng_mask_dark'></div>");
        $.ajax({
            url: "/admin/default/getfilteredemptycompanieslist",
            data: {companyName: companyName},
            type: "POST",
            success: function(msg) {
                $('#empty_companies_grid tbody').html(msg);
                self.companyID = 0;
                $('#empty_company_info').html('');
                self.initCompaniesGrid();
                $('#empty_companies > div .loadinng_mask_dark').remove();
            }
        });
    },

    /**
     * Get Company Info
     */
    getCompanyInfo: function (companyId) {
        var self = this;
        $('#empty_company_info').scrollTop(0).prepend("<div class='loadinng_mask'></div>");
        $.ajax({
            url: "/admin/default/getemptycompanyinfo",
            data: {companyId: companyId},
            type: "POST",
            success: function(msg) {
                $('#empty_company_info').html(msg);
                $('#empty_company_info .loadinng_mask').remove();
            }
        });
    }
 }