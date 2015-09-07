function LibraryView(activeTab, screen) {
    this.init(activeTab, screen);
}

LibraryView.prototype = {
    /**
     * Active tab
     */
    activeTab: 'tab1',

    /**
     * If mouse is down on slider
     */
    isMouseDown: false,

    /**
     * Last cursor position
     */
    top: 0,
    left: 0,

    /**
     * If slider was moved
     */
    wasMoved: false,

    /**
     * If email is valid
     */
    validMail: false,

    /**
     * If document corresponds to
     */
    validDocumentAccordance: 0,

    /**
     * Type of device
     */
    screen: 'desctop',

    /**
     * Initialize method
     */
    init: function(activeTab, screen) {
        var self = this;

        console.log ("Active tab",activeTab);
        self.screen = screen;

        $('.yiiTab > .view').each(function() {
            self.initTab($(this));
        });

        self.activeTab = activeTab;
        self.activeTab = self.activeTab.slice(1);

        $('.gallery_thumbs').each(function () {

            if (!$(this).hasClass(self.activeTab)) {
               $(this).hide();
            } else  {
                console.log($(this));
                console.log('has class '+self.activeTab);
                $(this).show();
            }
        });

       $('.tabs li a').click(function() {

           self.activeTab = $(this).attr('href');
           self.init(self.activeTab,self.screen);

            self.updateNavigation();
            self.setActionsButtons();
        });

        $('#current_item_switch_counter').bind('blur',function(){
            console.log ("blur event");
            var value = parseInt($(this).val());
            self.switchToDocumentNumber(value);

        });

        $('#current_item_switch_counter').bind('keypress',function(event){
            if (event.keyCode == 13) {
                $(this).blur();
            }
        });


        $('#activate_previous_doc').click(function() {
            console.log ("previous click");
            self.switchDocument('prev');
        });

        $('#activate_next_doc').click(function() {
            self.switchDocument('next');
        });

        if (screen == 'mobile') {
            self.initMobileSliderSwipe();
        } else {
            self.initDesctopSliderSwipe();
        }

        self.setActionsButtons();
        self.getAccessDropDown();

        $('#print_doc').click(function() {
            var active = $(this).attr('data-active');
            var doc_id = $(this).attr('data-id');
            if (active == 'yes') {
                $.ajax({
                    url: "/documents/setdocumentidtoprint",
                    data: {doc_id: doc_id},
                    type: "POST",
                    async: false,
                    success: function() {
                        var url = '/documents/printdocument';
                        window.open(url, 'popup', 'toolbar=no, menubar=no, status=no, location=no, resizable=no, width=640, height=600');
                    }
                });
            }
        });

        $('#send_email').click(function() {
            show_modal_box('#askemailbox');
        });

        $('#doc_to_user_email').blur(function() {
            self.checkEmail();
        });

        // Send document by email action
        $('#send_doc_by_email').click(function() {
            var doc_id = $('#send_email').attr('data-id');
            if (self.validMail && doc_id > 0) {
                close_modal_box('#askemailbox');
                var email = $('#doc_to_user_email').val();
                $.ajax({
                    url: "/documents/senddocumentbyemail",
                    data: {email: email, doc_id: doc_id},
                    type: "POST",
                    success: function(msg) {
                        if (msg == 1) {
                            setTimeout(function() {
                                show_alert("Email was sent!", 250);
                            }, 200);
                        } else {
                            setTimeout(function() {
                                show_alert("Email was not sent!", 250);
                            }, 200);
                        }
                        $('#doc_to_user_email').val('');
                    }
                });
            } else {
            $('#doc_to_user_email').focus();
            }
        });

        $('#duplicate').click(function() {
            self.showForm();
        });

        $('#po_sorting').change(function() {
            var value = $(this).val();
            $.ajax({
                url: "/library/setposorting",
                data: {
                    value: value
                },
                async: false,
                type: "POST",
                success: function() {
                    window.location = '/library/viewstorage';
                }
            });
        });

        $('#edit_dataentry').click(function() {
           var doc_id = $('#document_info_block').data('id');
            if ($(this).data('class')==2) {
               //show payroll dataentry
               $.ajax({
                   url: '/dataentry/AjaxPayrDataEntry',
                   data: {doc_id: doc_id },
                   type: 'POST',
                   success: function(html){
                       $('#dataentry_block').html(html);
                       show_modal_box('#dataentry_block', 260, 50);
                       $('#data_entry_left').css('min-height','265px');
                   }
               });
           }
           if ($(this).data('class')==3) {
                //show je dataentry
               $.ajax({
                   url: '/dataentry/AjaxJeDataEntry',
                   data: {doc_id: doc_id },
                   type: 'POST',
                   success: function(html){
                       $('#dataentry_block').html(html);
                       show_modal_box('#dataentry_block', 260, 50);
                       $('#data_entry_left').css('min-height','265px');
                   }
               });
           }

            if ($(this).data('class')==5) {
                //show ar dataentry
                $.ajax({
                    url: '/dataentry/AjaxArDataEntry',
                    data: {doc_id: doc_id },
                    type: 'POST',
                    success: function(html){
                        $('#dataentry_block').html(html);
                        show_modal_box('#dataentry_block', 260, 50);
                        $('#data_entry_left').css('min-height','285px');
                    }
                });
            }

        });

        $('#dataentry_block').on('click','#submit_ajax_payr_form',function (event){
            event.preventDefault();

            $('#data_entry_left').scrollTop(0).prepend("<div class='loadinng_mask' style='width: 330px;'></div>");



            $.ajax({
                type: 'POST',
                url: '/dataentry/AjaxPayrFromDetail',
                data: $('#payroll_data_entry_form').serialize(),
                dataType: 'json',
                success: function(data){
                    $('#dataentry_block').html(data.html);
                    $('.cancelbutton').click(function(){
                        //window.location ='/library/viewstorage'
                    });

                    console.log ("result", data.saved);

                    if(data.saved) {
                        $('.cancelbutton').trigger('click');
                    }

                    if(data.weekending_changed) {
                        window.location ='/library/viewstorage'
                    }
                }
            });
        });

        $('#dataentry_block').on('click','#submit_ajax_je_form',function (event){
            event.preventDefault();

            $('#data_entry_left').scrollTop(0).prepend("<div class='loadinng_mask' style='width: 330px;'></div>");

            $.ajax({
                type: 'POST',
                url: '/dataentry/AjaxJeFromDetail',
                data: $('#je_data_entry_form').serialize(),
                dataType: 'json',
                success: function(data){
                    $('#dataentry_block').html(data.html);
                    $('.cancelbutton').click(function(){
                        //window.location ='/library/viewstorage'
                    });

                    if(data.saved) {
                        $('.cancelbutton').trigger('click');
                    }

                    if(data.je_date_changed) {
                        window.location ='/library/viewstorage'
                    }
                }
            });
        });


        $('#dataentry_block').on('click','#submit_ajax_ar_form',function (event){
            event.preventDefault();

            $('#data_entry_left').scrollTop(0).prepend("<div class='loadinng_mask' style='width: 330px;'></div>");

            $.ajax({
                type: 'POST',
                url: '/dataentry/AjaxArFromDetail',
                data: $('#ar_data_entry_form').serialize(),
                dataType: 'json',
                success: function(data){

                    $('#dataentry_block').html(data.html);

                    $('.cancelbutton').click(function(){
                        //window.location ='/library/viewstorage'
                    });

                    if(data.saved) {
                        $('.cancelbutton').trigger('click');
                    }

                    if(data.ar_date_changed) {
                        window.location ='/library/viewstorage'
                    }
                }
            });
        });




    },

    /**
     * Initialize slider swipe for mobile devices
     */
    initMobileSliderSwipe: function() {
        $('.thumb_images').swipe(function(event, data) {
            event.stopImmediatePropagation();
            event.preventDefault();
            if (data.deltaX < 0) {
                $(this).parent().find('.scroll_right').trigger('click');
            } else {
                $(this).parent().find('.scroll_left').trigger('click');
            }
        });
    },

    /**
     * Initialize slider swipe for desctop
     */
    initDesctopSliderSwipe: function() {
        var self = this;

        $('.thumb_images').mousedown(function(event) {
            event.stopPropagation();
            self.isMouseDown = true;
            self.top = event.pageY;
            self.left = event.pageX;
            $(this).css('cursor', 'move');
        });

        $('body').mouseup(function(){
            if (self.isMouseDown) {
                self.setImgsSrc();
            }
            self.isMouseDown = false;
            $('.thumb_images').css('cursor', 'default');
        });

        $('.slider img').mousedown(function(event) {
            event.stopPropagation();
            self.isMouseDown = true;
            self.top = event.pageY;
            self.left = event.pageX;
            $(this).parent().parent().parent().css('cursor', 'move');
            event.preventDefault();
            return false;
        });

        $('.thumb_images').mousemove(function(event) {
            event.stopImmediatePropagation();
            event.preventDefault();
            if (self.isMouseDown) {
                self.wasMoved = true;
                var slider = $(this).find('.slider');
                var left = -parseInt(slider.css('left'));
                var width = slider.width();
                left -= (event.pageX - self.left);
                left = (left < 0) ? 0 : left;
                left = (left > (width - 880)) ? (width - 880) : left;
                slider.css('left', '-' + left + 'px');
                self.top = event.pageY;
                self.left = event.pageX;
                self.setArrowsStatuses();
            }
        });
    },

    /**
     * Initialize tab actions
     * @param tab
     */
    initTab: function(tab) {

        console.log ("inside initTab, tab= ",tab);
        var self = this;
        var id = tab.attr('id');

        self.initDocumentActions('#' + id);

        // process slider
        var slider = $('.' + id + '.slider');
        var countImages = slider.find('.doc_thumb_image').length;

        if (countImages > 7) {
            slider.width(countImages*112);
        } else {
            slider.attr('data-active', 'no');
            slider.parent().parent().find('.scroll_left_block img').remove();
            slider.parent().parent().find('.scroll_right_block img').remove();
        }

        $('.' + id + '.slider .doc_thumb_image').hover(
            function() {
                var active = $(this).attr('data-active');
                if (active == 'no') {
                    $(this).fadeTo(200, 0.8);
                }
            }, function() {
                var active = $(this).attr('data-active');
                if (active == 'no') {
                    $(this).fadeTo(200, 0.5);
                }
            }
        );

        $('.' + id + '.slider .doc_thumb_image').click(
            function() {
                var active = $(this).attr('data-active');
                if (active == 'no') {
                    if (self.wasMoved) {
                        self.wasMoved = false;
                    } else {
                        self.activateDocument($(this));
                    }
                }
            }
        );

        $('.' +id + '.scroll_left').click(
            function() {
                var active = $(this).attr('data-active');
                if (active == 'yes') {
                    self.scrollLeft();
                }
            }
        );

        $('.' + id + '.scroll_right').click(
            function() {
                var active = $(this).attr('data-active');
                if (active == 'yes') {
                    self.scrollRight();
                }
            }
        );
    },

    /**
     * Initialize document view actions
     * @param tab
     */
    initDocumentActions: function(tab) {
        var document_view_block = $(tab + ' .gallery_main_container').attr('id');
        var block_to_move = $(tab + ' .document_block').attr('id');
        var file_view_block = $(tab + ' .gallery_detail_block').attr('id');

        new DocumentView('#' + document_view_block, '#' + block_to_move, '#' + file_view_block, 850, 45,  10);
    },

    /**
     * Initialize duplicate document form
     */
    initDuplicateForm: function() {
        var self = this;

        $('#duplicateform').submit(function(event) {
            event.preventDefault();
        });

        $('#new_storage_type').change(function() {
            var value = $(this).val();
            if (value == 0) {
                $('#new_storage').val(0).attr('disabled', true);
                $('#new_section').val(0).attr('disabled', true);
                $('#new_subsection').val(0).attr('disabled', true);
            } else {
                self.getStorages('storages', value);
            }
        });

        $('#new_storage').change(function() {
            var value = $(this).val();
            if (value == 0) {
                $('#new_section').val(0).attr('disabled', true);
                $('#new_subsection').val(0).attr('disabled', true);
            } else {
                self.getStorages('sections', value);
            }
        });

        $('#new_section').change(function() {
            var value = $(this).val();
            if (value == 0) {
                $('#new_subsection').val(0).attr('disabled', true);
            } else {
                self.getStorages('subsections', value);
            }
        });

        $('#submit_duplicate').click(function() {
            var subsectionID = $('#new_subsection').val();
            var sectionID = $('#new_section').val();
            var docId = $("#"+self.activeTab + ' .document_block').attr('data-id');
            var currentSubsection = $("#"+self.activeTab + ' .document_block').attr('data-subsid');
            var action = $('#duplicate_type').val();

            if (subsectionID == 0) {
                $('#new_subsection_error').text('Panel/Tab is required').show();
            } else {
                self.checkDocumentAccordanceToSection(sectionID, docId);
                if (self.validDocumentAccordance == '1') {
                    $('#new_subsection_error').text('').hide();
                    self.duplicateDocument(subsectionID, docId, currentSubsection, action);
                } else {
                    $('#new_subsection_error').text('Document can not be placed in selected Panel/Tab. Document type does not correspond to folder/binder category.').show();
                }
            }
        });
    },

    /**
     * Initialize dropdown to set access to LB docs
     */
    initAccessDropDown: function() {
        var self = this;
        $('#dropdownaccess_sel').change(function() {
            var docId = $(self.activeTab + ' .document_block').attr('data-id');
            var subsectionID = $(self.activeTab + ' .document_block').attr('data-subsid');
            var value = $(this).val();
            $.ajax({
                url: "/library/setaccesstodoc",
                data: {
                    docId: docId,
                    subsectionID: subsectionID,
                    value: value
                },
                async: false,
                type: "POST",
                success: function() {

                }
            });
        });
    },

    /**
     * Switch to previous or next document
     * @param direction
     */
    switchDocument: function(direction) {
        var slider = $('.'+this.activeTab + '.slider');
        console.log("slider found",slider);
        var length = slider.find('.doc_thumb_image').length;
        var number = slider.find('.doc_thumb_image[data-active=yes]').attr('data-numb');
        console.log ("number",number);
        var activeDocument = slider.find('.doc_thumb_image[data-active=yes]');
        console.log ("active Doc",activeDocument);
        var documentToActivate = false;
        if (direction == 'next') {
            if (number == length) {
                documentToActivate = slider.find('.doc_thumb_image').first();
            } else {
                documentToActivate = activeDocument.next();
            }
        } else if (direction == 'prev') {
            if (number == 1) {
                documentToActivate = slider.find('.doc_thumb_image').last();
            } else {
                documentToActivate = activeDocument.prev();
            }
        }

        console.log (" Doc to activate",documentToActivate);
        if (documentToActivate !== false) {
            this.activateDocument(documentToActivate);
        }
    },

    /**
     * Switch to previous or next document
     * @param direction
     */
    switchToDocumentNumber: function(number_to_switch) {
        var slider = $('.'+this.activeTab + '.slider');
        console.log("slider found",slider);
        var length = slider.find('.doc_thumb_image').length;
        console.log("length of slider",length);

        if (number_to_switch > length) {
            number_to_switch = length;
        }

        if (number_to_switch < 1) {
            number_to_switch = 1;
        }


        documentToActivate = slider.find('.doc_thumb_image').eq(number_to_switch-1);

        console.log ("Document to activate",documentToActivate);

        /*var number = slider.find('.doc_thumb_image[data-active=yes]').attr('data-numb');
        var activeDocument = slider.find('.doc_thumb_image[data-active=yes]');
        var documentToActivate = false;
        if (direction == 'next') {
            if (number == length) {
                documentToActivate = slider.find('.doc_thumb_image').first();
            } else {
                documentToActivate = activeDocument.next();
            }
        } else if (direction == 'prev') {
            if (number == 1) {
                documentToActivate = slider.find('.doc_thumb_image').last();
            } else {
                documentToActivate = activeDocument.prev();
            }
        }*/

        if (documentToActivate !== false) {
            this.activateDocument(documentToActivate);
        }
    },

    /**
     * Activate document
     */
    activateDocument: function(thumb) {
        console.log ("inside activate document thumb",thumb);
        var id = thumb.attr('data-id');
        console.log("idd ",id);
        var numb = thumb.attr('data-numb');
        thumb.parent().find('.doc_thumb_image[data-active=yes]').attr('data-active', 'no').fadeTo(200, 0.5);
        thumb.fadeTo(200, 1).attr('data-active', 'yes');
        this.getDocumentView(id);
        this.scrollSliderToImagePosition(numb);
        this.updateNavigation();
    },

    /**
     * Get document view by ID
     * @param id
     */
    getDocumentView: function(id) {
    console.log("Inside get document view");
        var self = this;
        var docContainer = $('#'+self.activeTab + ' .gallery_main_container');
        var containerNum = docContainer.attr('data-num');
        var subsectionId = $('#'+self.activeTab + ' .document_block').attr('data-subsid');
        $.ajax({
            url: "/library/getdocumentview",
            data: {
                doc_id: id,
                tab_num: containerNum,
                subsectionId: subsectionId
            },
            type: "POST",
            async: false,
            success: function(msg) {
                if (msg == '') {
                    docContainer.html('<p class="no_images">Document were not found.</p>');
                } else {
                    docContainer.html(msg);
                    self.initDocumentActions(self.activeTab);
                    self.setActionsButtons();
                    self.getAccessDropDown();
                }
            }
        });
    },

    /**
     * Scroll slider to certain image
     * @param imgNumber
     */
    scrollSliderToImagePosition: function(imgNumber) {
        var self = this;
        var slider = $('.'+this.activeTab + '.slider');
        var active = slider.attr('data-active');
        var left = -parseInt(slider.css('left'));

        if (active == 'yes') {
            if ((left + 880) < imgNumber*112) {
                slider.animate({left: '-' + (imgNumber*112-880) + 'px'}, 200);
            } else if (left > (imgNumber-1)*112) {
                slider.animate({left: '-' + ((imgNumber-1)*112) + 'px'}, 200);
            }
        }

        setTimeout(function() {
            self.setArrowsStatuses();
            self.setImgsSrc();
        }, 210);
    },

    /**
     * Scroll slider to left
     */
    scrollLeft: function() {
        var self = this;
        var slider = $('.'+this.activeTab + '.slider');
        var active = slider.attr('data-active');
        var left = -parseInt(slider.css('left'));

        left = left - 4*112;
        if (left < 0) {
            left = 0;
        }

        if (active == 'yes') {
            slider.animate({left: '-' + left + 'px'}, 200);
        }

        setTimeout(function() {
            self.setArrowsStatuses();
            self.setImgsSrc();
        }, 210);
    },

    /**
     * Scroll slider to right
     */
    scrollRight: function() {
        var self = this;
        var slider = $('.'+this.activeTab + '.slider');
        var active = slider.attr('data-active');
        var left = -parseInt(slider.css('left'));
        var width = slider.width();

        left = left + 4*112;
        if ((left+880) > width) {
            left = width-880;
        }

        if (active == 'yes') {
            slider.animate({left: '-' + left + 'px'}, 200);
        }

        setTimeout(function() {
            self.setArrowsStatuses();
            self.setImgsSrc();
        }, 210);
    },

    /**
     * Activate or deactivate slider's arrows
     */
    setArrowsStatuses: function() {
        var slider = $('.'+this.activeTab + '.slider');
        var active = slider.attr('data-active');
        var left = -parseInt(slider.css('left'));
        var width = slider.width();

        if (active == 'yes') {
            if ((left + 880) >= width) {
                $('.'+this.activeTab + '.scroll_left').attr('data-active', 'yes').removeClass('not_active_arrow').fadeTo(200, 1);
                $('.'+this.activeTab + '.scroll_right').attr('data-active', 'no').addClass('not_active_arrow').fadeTo(200, 0.3);
            } else if (left <= 0) {
                $('.'+this.activeTab + '.scroll_left').attr('data-active', 'no').addClass('not_active_arrow').fadeTo(200, 0.3);
                $('.'+this.activeTab + '.scroll_right').attr('data-active', 'yes').removeClass('not_active_arrow').fadeTo(200, 1);
            } else {
                $('.'+this.activeTab + '.scroll_left').attr('data-active', 'yes').removeClass('not_active_arrow').fadeTo(200, 1);
                $('.'+this.activeTab + '.scroll_right').attr('data-active', 'yes').removeClass('not_active_arrow').fadeTo(200, 1);
            }
        }
    },

    /**
     * Activate or deactivate slider's arrows
     */
    setImgsSrc: function() {
        var slider = $('.'+this.activeTab + '.slider');
        var left = -parseInt(slider.css('left'));

        var firstImageNumb = parseInt(left/112) - 4;
        firstImageNumb = firstImageNumb < 1 ? 1 : firstImageNumb;
        for (var i = firstImageNumb; i <= firstImageNumb + 20; i++) {
            var img = $('.'+this.activeTab + '.slider .doc_thumb_image[data-numb=' + i + '] img');
            var dataSrc = img.data('src');
            var src = img.attr('src');
            if (!src) {
                img.attr('src', dataSrc);
            }
        }
    },

    /**
     * Update navigation links counter
     */
    updateNavigation: function() {
        var slider = $('.'+this.activeTab + '.slider');
        var length = slider.find('.doc_thumb_image').length;
        if (length > 0) {
            $('#items_switch').show();
            var number = slider.find('.doc_thumb_image[data-active=yes]').attr('data-numb');
            //$('#items_switch_counter').text(number + ' of ' + length);
            $('#items_switch_counter input').val(number);
        } else {
            $('#items_switch').hide();
        }
    },

    /**
     * Activate or deactivate print button
     */
    setActionsButtons: function() {
        var mimeType = $('#'+this.activeTab + ' .document_block').attr('data-mime-type');
        var id = $('#'+this.activeTab + ' .document_block').attr('data-id');
        if (!mimeType) {
            $('#duplicate').hide();
            $('#print_doc').hide();
            $('#send_email').hide();
        } else if (mimeType == 'application/pdf') {
            $('#duplicate').show();
            $('#print_doc').hide().attr('data-active', 'no').attr('data-id', '0');
            $('#send_email').show().attr('data-id', id);
        } else {
            $('#duplicate').show();
            $('#print_doc').show().attr('data-active', 'yes').attr('data-id', id);
            $('#send_email').show().attr('data-id', id);
        }
    },

    /**
     *
     */
    getAccessDropDown: function() {
        var self = this;
        var docId = $(this.activeTab + ' .document_block').attr('data-id');
        var subsectionID = $(this.activeTab + ' .document_block').attr('data-subsid');
        $.ajax({
            url: "/library/getaccessdropdown",
            data: {
                docId: docId,
                subsectionID: subsectionID
            },
            async: false,
            type: "POST",
            success: function(msg) {
                if (msg == '') {
                    $('#dropdownaccess_block').hide();
                    $('#dropdownaccess').html('');
                } else {
                    $('#dropdownaccess_block').show();
                    $('#dropdownaccess').html(msg);
                    self.initAccessDropDown();
                }

            },
            error: function() {
                $('#dropdownaccess_block').hide();
                $('#dropdownaccess').html('');
            }
        });
    },

    /**
     * Check email
     */
    checkEmail: function () {
        var email = $('#doc_to_user_email').val();
        var pattern = /^([0-9a-zA-Z]([\-\.\w]*[0-9a-zA-Z])*@([0-9a-zA-Z][\-\w]*[0-9a-zA-Z]\.)+[a-zA-Z]{2,9})$/;
        if (!pattern.test(email)) {
            this.validMail = false;
            if (email != '') {
                $('#askemailbox .errorMessage').show();
            } else {
                $('#askemailbox .errorMessage').hide();
            }
        } else {
            this.validMail = true;
            $('#askemailbox .errorMessage').hide();
        }
    },

    /**
     * Show modal box with necessary form
     */
    showForm: function() {
        var self = this;
        $.ajax({
            url: "/library/getduplicatedocumentform",
            async: false,
            type: "POST",
            success: function(msg) {
                $('#library_form_modal').html(msg);
                show_modal_box('#library_form_modal');
                self.initDuplicateForm();
            }
        });
    },

    /**
     * Get storages for duplicate document form
     * @param storageType
     * @param value
     */
    getStorages: function(storageType, value) {
        var self = this;
        $.ajax({
            url: "/library/getstorages",
            data: {
                storageType: storageType,
                value: value
            },
            async: false,
            type: "POST",
            success: function(msg) {
                if (storageType == 'storages') {
                    $('#new_storage').html(msg).attr('disabled', false);
                    $('#new_section').val('0').attr('disabled', true);
                    $('#new_subsection').val('0').attr('disabled', true);
                } else if (storageType == 'sections') {
                    $('#new_section').html(msg).attr('disabled', false);
                    $('#new_subsection').val('0').attr('disabled', true);
                } else if (storageType == 'subsections') {
                    $('#new_subsection').html(msg).attr('disabled', false);
                }
            }
        });
    },

    /**
     * Check document Accordance To Section
     * @param sectionID
     * @param docId
     */
    checkDocumentAccordanceToSection: function(sectionID, docId) {
        var self = this;
        $.ajax({
            url: "/library/checkdocumentaccordancetosection",
            data: {
                sectionID: sectionID,
                docId: docId
            },
            async: false,
            type: "POST",
            success: function(msg) {
                self.validDocumentAccordance = msg;
            }
        });
    },

    /**
     * Copy or move document to certain item
     * @param subsectionID
     * @param docId
     * @param currentSubsection
     * @param action
     */
    duplicateDocument: function(subsectionID, docId, currentSubsection, action) {
        var self = this;
        $.ajax({
            url: "/library/duplicatedocument",
            data: {
                subsectionID: subsectionID,
                docId: docId,
                currentSubsection: currentSubsection,
                action: action
            },
            async: false,
            type: "POST",
            success: function() {
                window.location = '/library/viewstorage';
            }
        });
    }
}