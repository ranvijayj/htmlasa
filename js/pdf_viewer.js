/**
 * Created by lee on 9/18/14.
 */
function AsaPdfViewer (action,docId) {
    var self = this;
    this.init(action,docId);


}

AsaPdfViewer.prototype = {
    doc_id:null,

    context:null,
    imageObj:null,



    //pdf js variables
    pdfDoc : null,
    pageNum : 1,
    pageRendering : false,
    pageNumPending : null,
    scale : 0.98,
    rotateDeg:0,
    canvas: null,
    ctx: null,


    init: function(action,docId,isstring) {



        var self = this;
        console.log ("inside init");
        self.doc_id=docId;

        self.initButtonsBar();
        self.initPdfJs();





    },


    initButtonsBar: function(){
        var self = this;

    $('#rotate_button_cw').click(function(){
          self.rotateDeg+=90;
          self.renderPage(self.pageNum);
      });

    $('#rotate_button_ccw').click(function(){
            self.rotateDeg-=90;
            self.renderPage(self.pageNum);
        });

    $('#autofit').click(function(){
            self.scale=0.90;
            self.reinitPdfJs();
        });

    $('#fittowidth').click(function(){
            self.scale=0.98;
            self.reinitPdfJs();
        });

    $('#zoom-in').click(function(){
            self.scale+=0.15;
            self.reinitPdfJs();
        });

    $('#zoom-out').click(function(){
            self.scale-=0.15;
            self.reinitPdfJs();

    });

    $('#print_button').click(function(){
        window.print() ;


    });

    },



    reinitPdfJs: function (){
        var self = this;
        $('#canvas_wrapper').html('');
        self.pdfDoc = null;
        self.initPdfJs();

    },

    //most of logic below is from PDF.js example
    initPdfJs: function (){
        var self = this;
        var url ='';
        var sourse = Number(self.doc_id);

            if (!isNaN(sourse)) {
                url = '/documents/getdocumentfile?doc_id='+self.doc_id;
                console.log ("Number "+ url);
            } else {
                url = '/documents/getdocumentfilebypath?doc_id='+String(self.doc_id);
                console.log ("String "+ url);
            }
        //console.log(url);
        //if  the pdf.js is executed via eval(), the workerSrc property shall be specified.
        PDFJS.workerSrc = '/js/pdf_js/build//pdf.worker.js';
        //variables



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

            //uncomment
            var viewer = document.getElementById('canvas_wrapper');
            self.pageNum = 1;
            self.renderPage(viewer,self.pageNum++,function pageRenderingComplete() {
                    if (self.pageNum > self.pdfDoc.numPages) {
                        return; // All pages rendered
                    }
                    // Continue rendering of the next page
                    self.renderPage(viewer, self.pageNum++, pageRenderingComplete);
            });

        });


    },

   renderPage: function (div,num,callback) {
        var self = this;

        self.pageRendering = true;

        // Using promise to fetch the page
        self.pdfDoc.getPage(num).then(function(page) {



                var viewport = page.getViewport(self.scale,self.rotateDeg);

                //self.canvas.height = viewport.height;
                //self.canvas.width = viewport.width;

                //creating new 'page'
                var pageDivHolder = document.createElement('div');

                    pageDivHolder.className = 'pdfPage';
                    pageDivHolder.style.width = viewport.width + 'px';
                    pageDivHolder.style.height = viewport.height + 'px';
                    div.appendChild(pageDivHolder);

            // Prepare canvas using PDF page dimensions
            var canvas = document.createElement('canvas');
            self.ctx = canvas.getContext('2d');
            canvas.width = viewport.width;
            canvas.height = viewport.height;

            pageDivHolder.appendChild(canvas);

            //prepare div for text selection
            var textLayerDiv = document.createElement('div');
            textLayerDiv.className = 'textLayer';
            textLayerDiv.style.width = canvas.width;
            textLayerDiv.style.height = canvas.height;
            pageDivHolder.appendChild(textLayerDiv);


            // Render PDF page into canvas context
                var renderContext = {
                    canvasContext: self.ctx,
                    viewport: viewport
                };


            // ... and at the same time, getting the text and creating the text layer.
            var textLayerPromise = page.getTextContent().then(function (textContent) {
                var textLayerBuilder = new TextLayerBuilder({
                    textLayerDiv: textLayerDiv,
                    viewport: viewport,
                    pageIndex: 0
                });
                textLayerBuilder.setTextContent(textContent);
            });

            var breakDiv = document.createElement('div');
            breakDiv.className = 'breakDiv';
            breakDiv.style.width = canvas.width;
            breakDiv.style.height = 10+'px';
            div.appendChild(breakDiv);

            var renderTask = page.render(renderContext).promise.then(callback);
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


    }

}