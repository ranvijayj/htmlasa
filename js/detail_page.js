function DetailPage() {
    this.init();
    this.initDeleteDocumentButton();
}

DetailPage.prototype = {
    /**
     * Current page
     */
    page: '',

    /**
     * Is valid email
     */
    validMail: false,

    /**
     * Is valid fax
     */
    validFax: false,

    /*
     * Initialize method
     */
    init: function() {
        // body of this method must be defined in child classes
    },

    /**
     * Init button for deleting of documents
     */
    initDeleteDocumentButton: function() {
        $('#delete_document').click(function() {
            var href = $(this).data('href');
            $('#dialogmodal a').attr("href", href);
            show_dialog('Are you sure you want to delete this document?', 500);
        });
    },

    /**
     * Add note
     */
    addNote: function() {
        var self = this;
        var comment = $('#notes_blok .note_textarea').val();
        var docId = $('#notes_blok #note_to_document').val();
        $('#notes_blok .note_textarea').val('');
        self.docId = docId;
        if (comment != '') {
            $.ajax({
                url: "/" + self.page + "/addnote",
                data: {comment: comment, docId: docId},
                type: "POST",
                success: function(msg) {
                    $('#notes_list').prepend(msg);
                    $('#notes_list .note_item:first-child').effect('highlight');
                }
            });
        } else {
            $('#notes_blok .note_textarea').focus();
        }
    },

    /**
     * Check email
     */
    checkEmail: function () {
        var email = $('#doc_to_user_email').val();
        email = email.replace(/\s+/g, ''); //removing whitespaces

        var emails_array = email.split(';');
        var pattern = /^([0-9a-zA-Z]([\-\.\w]*[0-9a-zA-Z])*@([0-9a-zA-Z][\-\w]*[0-9a-zA-Z]\.)+[a-zA-Z]{2,9})$/;

        for (var i = 0; i < emails_array.length; i++) {
            if (!pattern.test(emails_array[i])) {
                this.validMail = false;
                if (emails_array[i] != '') {
                    $('#askemailbox .errorMessage').show();
                } else {
                    $('#askemailbox .errorMessage').hide();
                }
            } else {
                this.validMail = true;
                $('#askemailbox .errorMessage').hide();
            }
        }
    },



    /**
     * Check fax
     */
    checkFax: function () {
        var email = $('#doc_to_user_fax').val();
        var pattern = /^\+\d{12}$/;
        if (!pattern.test(email)) {
            this.validFax = false;
            if (email != '') {
                $('#askfaxbox .errorMessage').show();
            } else {
                $('#askfaxbox .errorMessage').hide();
            }
        } else {
            this.validFax = true;
            $('#askfaxbox .errorMessage').hide();
        }
    }
}