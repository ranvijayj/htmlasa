/**
 * Created by lee on 9/18/14.
 */
function FileModification (action,docId,isstring,url) {
    var self = this;
    this.init(action,docId,isstring,url);


}

FileModification.prototype = {
    doc_id:null,
    url:null,

    context:null,
    imageObj:null,



    //pdf js variables
    pdfDoc : null,
    pageNum : 1,
    pageRendering : false,
    pageNumPending : null,
    scale : 0.8,
    rotateDeg:0,
    canvas: null,
    ctx: null,


    init: function(action,docId,isstring,url) {

        var self = this;
        self.url = url;
        self.doc_id=docId;
        if(!isstring) self.docId= Number(docId);
        if(isstring) self.docId= String(docId);
        if (action=='rotate_button_cw' || action=='rotate_button_ccw'){
            self.rotate(action,isstring)
        }

        if (action=='reupload-icon'){

            $('#dialogmodal_de h2').text("Do you really want to replace this document by another file?");
            show_modal_box('#dialogmodal_de',550);

            $('#yesbutton').unbind('click',function (){});
            $('#yesbutton').bind('click',function(e) {
                e.preventDefault();


                $('#fileupload').click(); // run  jquery plugin for fileiploading

                var url = '/uploads/UploadStatus';//action for handling uploaded files
                $('#fileupload').fileupload({
                    url:url,
                    dataType: 'json',

                    done: function (e, data) {

                        $.each(data.result.files, function (index, file) {
                            var jsonString = JSON.stringify(file);
                            $.ajax({
                                type:"POST",
                                url: '/uploads/ReplaceImage',
                                data: {files : jsonString,doc_id:self.doc_id},
                                success: function (html) {
                                    var url = self.getCurrentUrl();
                                    window.location= url; //refresh page
                                }
                            });
                        });


                    }
                });

                close_modal_box('#dialogmodal_de');
            });


        }

        if (action=='delete-icon'){
            $('#dialogmodal_de h2').text("Do you really want to delete this Document?");

            $('#yesbutton').unbind('click',function (){});
            $('#yesbutton').bind('click',function(e) {
                e.preventDefault;


                $.ajax({
                    type:"POST",
                    url: '/documents/DeleteDocumentAjax',
                    data: {
                        doc_id: self.doc_id
                    },
                    dataType: 'json',

                    success: function (result) {

                        console.log("Success ",result);
                        console.log("Success ",result.location);
                        if (result['success']){
                            console.log("inside");
                            console.log(result['location']);

                             window.location = result['location'];
                        }
                        close_modal_box('#dialogmodal_de');
                    }
                });

            });

            show_modal_box('#dialogmodal_de',500);
        }

        $("#zoom-in")
        if (action=='zoom-in' ){
            self.zoomIn()
        }
        if (action=='zoom-out'){
            self.zoomOut()
        }


    },

    rotate:function (action,isstring){
        var self = this;
        if (isstring) {var url='/file/RotateNotSaved'; }
        if (!isstring) {var url='/file/rotate' ;}

        var node;
            $.ajax({
            type:"POST",
            url: url,
            data: {
                    docID: self.docId,
                    action:action
                    },
            success: function (msg) {
                obj = JSON.parse(msg);
                var iframe = $('#embeded_pdf iframe').attr('src','/documents/PreviewFile?file_id='+obj.file_id);
            },
            error: function () {
                node.html('File was not converted and rotated. Probably you have problems with your file - ');
            }
        });
    },

    reinitButtonsBar: function(){
        var self = this;

    $('#rotate_button_cw').click(function(){
          self.rotateDeg+=90;
          self.renderPage(self.pageNum);
      });

    $('#rotate_button_ccw').click(function(){
            self.rotateDeg-=90;
            self.renderPage(self.pageNum);
        });


    $('#zoom-in').click(function(){
            self.scale+=0.05;
            self.renderPage(self.pageNum);
        });
    $('#zoom-out').click(function(){
            self.scale-=0.05;
            self.renderPage(self.pageNum);
    });

    },


    showCanvas: function (docId) {
        var self = this;
        $.ajax({
            url: '/file/showcanvas',
            data: {
                docId: docId
            },
            async: false,
            type: "POST",
            success: function(html) {
                if (html) {
                    $('#canvas_block').html(html);
                    self.initPdfJs();
                    show_modification_box('#canvas_block');
                    self.reinitButtonsBar();


                }

            }
        });
    },

    //most of logic below is from PDF.js example
    initPdfJs: function (){
        var self = this;
        console.log(self.pageRendering);
        var url = '/documents/getdocumentfile?doc_id='+self.doc_id;
        console.log(url);
        //if  the pdf.js is executed via eval(), the workerSrc property shall be specified.
        //PDFJS.workerSrc = '/js/pdf_js/build//pdf.worker.js';
        PDFJS.workerSrc =  self.url+'/build/pdf.worker.js';
        //variables

         self.canvas = document.getElementById('the-canvas');
         self.ctx = self.canvas.getContext('2d');


        //on page click functions assign
        document.getElementById('prev').addEventListener('click', function(){
            self.onPrevPage();
            console.log('Click');
        });
        document.getElementById('next').addEventListener('click', function(){
            self.onNextPage();
            console.log('Click Next');
        });



        //Asynchronously downloads PDF.
        PDFJS.getDocument(url).then(function (pdfDoc_) {
            self.pdfDoc = pdfDoc_;

            document.getElementById('page_count').textContent = self.pdfDoc.numPages;
            self.numPages = self.pdfDoc.numPages;

            // Initial/first page rendering
            self.renderPage(self.pageNum);
        });



    },

    renderPage: function (num) {
        var self = this;
        self.pageRendering = true;
        // Using promise to fetch the page
        self.pdfDoc.getPage(num).then(function(page) {

                var viewport = page.getViewport(self.scale,self.rotateDeg);
                self.canvas.height = viewport.height;
                self.canvas.width = viewport.width;

                // Render PDF page into canvas context
                var renderContext = {
                    canvasContext: self.ctx,
                    viewport: viewport
                };
                var renderTask = page.render(renderContext);

                // Wait for rendering to finish
                renderTask.promise.then(function () {
                    self.pageRendering = false;
                    if (pageNumPending !== null) {
                        // New page rendering is pending
                        self.renderPage(self.pageNumPending);
                        self.pageNumPending = null;
                    }
                });
        });

        // Update page counters
        document.getElementById('page_num').textContent = self.pageNum;

    },

    /**
     * If another page rendering in progress, waits until the rendering is
     * finised. Otherwise, executes rendering immediately.
     */
    queueRenderPage: function(num) {
        console.log("inside queueue "+num);
        var self = this;
        console.log(self.pageRendering);
        if (self.pageRendering) {
            self.pageNumPending = num;
        } else {
            console.log("Trying to render page "+num);
            self.renderPage(num);
        }
    },

    /**
     * Displays previous page.
     */
    onPrevPage: function() {
        var self = this;
        if (self.pageNum <= 1) {
            return;
        }
        self.pageNum--;
        self.queueRenderPage(self.pageNum);
    },


    /**
     * Displays next page.
     */
    onNextPage: function () {
        var self = this;
        console.log("Object num pages "+self.pdfDoc.numPages);
        console.log("Curr page num "+self.pageNum);
        if (self.pageNum >= self.pdfDoc.numPages) {
                return;
            }
            self.pageNum++;
        console.log("Curr page num "+self.pageNum);
            self.queueRenderPage(self.pageNum);
    },




zoomIn: function() {
        var self = this;
        self.scale=self.scale*1.1;

        console.log("Zoom in");
        self.redrawCanvas();

    },

    redrawCanvas: function(){
        self.canvas = document.getElementById('canvas');
        console.log("Founded canvas "+self.canvas);
        self.context = self.canvas.getContext("2d");
        var background = document.getElementById('background');
        console.log("Founded background "+background.src);

        self.imageObj = new Image();

        self.imageObj.onload = function() {
            self.canvas.width=630;
            self.canvas.height=891;
            self.context.drawImage(self.imageObj,50,50,self.imageObj.width*self.scale,self.imageObj.height*self.scale,0,0,630*self.scale,891*self.scale);
        };
        self.imageObj.src = background.src;


    },

    getCurrentUrl: function () {
        var value = parseInt($('.in_place_edit').html());
        var url= $('.items_switch_de a.button').attr('href');
        var url_arr = url.split('?');

        return url_arr[0]+'?page='+value;

    }

}