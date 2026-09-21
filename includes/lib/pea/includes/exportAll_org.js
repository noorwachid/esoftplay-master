// pack -cw exportAll_org.js exportAll.js
// https://jscompress.com

var a = document.createElement("script");
a.setAttribute("src", "https://cdn.sheetjs.com/xlsx-0.20.3/package/dist/xlsx.full.min.js");
document.head.appendChild(a);
var pXls = {};
_Bbc(function($){
	$(".fa-lg", $(".roll-export")).on("click", function(e){
		e.preventDefault();
		var rel = $(this).attr("rel").split('=');
		var name = rel[0];
		var title = decodeURIComponent(rel[1].replace(/\+/g, ' '));
		var type = $(this).data("type");


		var checkbox = $(".export_all", $(this).closest(".roll-export"));
		var getPage = $(checkbox).data("page");
		var getForm = $(checkbox).data("form");
		var regex = null;
		var url = document.location.href;
		if (checkbox.is(":checked")) {
			regex = new RegExp("([\?&]"+getPage+"=[0-9]+)", "g");
			url = url.replace(regex, "");
			title = title.substring(0, title.lastIndexOf(' - Page') + 1);
			page = 1;
		}else{
			regex = new RegExp("[\?&]"+getPage+"=([0-9]+)", "g");
			X = regex.exec(url);
			page = 1;
			if (X != null) {
				if (X[1] > 0) {
					page=X[1];
				}
			}
		}
		url += url.match(/\?/) ? "&" : "?";
		url += getForm + "_export_all=";
		url += checkbox.is(":checked") ? "1" : "0";
		url += "&" + getForm + "_export_type="+type;
		url += "&" + getPage + "=";
		f = '<div class="modal fade" tabindex="-1" role="dialog" id="export_'+getForm+'">'
				+'<div class="modal-dialog" role="document">'
					+'<div class="modal-content">'
						+'<div class="modal-header">'
							+'<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
							+'<h4 class="modal-title">Extracting Data..</h4>'
						+'</div>'
						+'<div class="modal-body"></div>'
						+'<div class="modal-footer">'
							+'<button type="button" class="btn btn-default btn-secondary" data-dismiss="modal">Cancel</button>'
						+'</div>'
					+'</div>'
				+'</div>'
			+'</div>';
		$(document.body).append(f);
		var modal = $("#export_"+getForm);
		var body = $(".modal-body", modal);
		modal.on("show.bs.modal", function(e){
			window[$(this).prop("id")] = true;
		}).on("hide.bs.modal", function(e){
			window[$(this).prop("id")] = false;
			$(this).remove();
		});
		modal.modal("show");
		pXls = {
			tot: {
				header: 0,
				rows: 0
			},
			data: {
				header: [],
				body: []
			}
		}
		peaExtract(url, page, modal, body, name, title.trim(), type);
	});

	$(".fa-lg", $(".edit-export")).on("click", function(e){
		e.preventDefault();
		var title = $(this).data("title").replace(/\+/g, ' ').trim();
		var type = $(this).data("type");
		var form = $(this).data("form");
		var url = document.location.href;
		url += url.match(/\?/) ? "&" : "?";
		url += "&" + form + "_export_type="+type;
		$.ajax(url,{
			success: function(out) {
				if (out.data) {
					output = '';
					if (type == 'html') {
						output = htmlOpen(title);
					}
					output += out.data;
					if (type == 'html') {
						output += htmlClose();
						ext = 'html';
					}else{
						ext = 'csv';
					}
					var blob = new Blob([output], { type: 'text/'+ext });
					var href = window.URL.createObjectURL(blob);
					var b = document.createElement('a');
					b.setAttribute('href', href);
					b.setAttribute('download', title+'.'+ext);
					b.click();
				}
			}
		});
	});

	var htmlOpen = function(title) {
		return '<!DOCTYPE html>'
						+'<html lang="en">'
							+'<head>'
								+'<meta charset="utf-8">'
								+'<meta http-equiv="X-UA-Compatible" content="IE=edge">'
								+'<meta name="viewport" content="width=device-width, initial-scale=1">'
								+'<title>'+title+'</title>'
								+'<link href="'+_URL+'templates/admin/bootstrap/css/bootstrap.min.css" rel="stylesheet" />'
								+'<script type="text/javascript">var _ROOT="'+_ROOT+'";var _URL="'+_URL+'";function _Bbc(a,b){var c="BS3load_func";if(!window[c+"i"]){window[c+"i"]=0};window[c+"i"]++;if(!b){b=c+"i"+window[c+"i"]};if(!window[c]){window[c]=b}else{window[c]+=","+b}window[b]=a;if(typeof BS3!="undefined"){window[b](BS3)}};</script>'
							+'</head>'
							+'<body>'
								+'<table class="table table-striped table-bordered table-hover">';
	};
	var htmlClose = function(){
		return '</tbody></table>'
					+'<script src="'+_URL+'templates/admin/bootstrap/js/bootstrap.min.js"></script>'
				+'</body>'
			+'</html>';
	};
	var isNumeric = function(str) {
		if (typeof str != "string") return false // we only process strings!
		return !isNaN(str) && // use type coercion to parse the _entirety_ of the string (`parseFloat` alone does not do this)...
					!isNaN(parseFloat(str)) // ...and ensure strings of whitespace fail
	};
	var peaExtract = function(url, page, modal, body, name, title, type) {
		$.ajax(url+page,{
			global: false,
			success: function(out) {
				if (window[$(modal).prop("id")]) {
					if ( typeof out.done != 'undefined') {
						body.html('<div class="progress"><div class="progress-bar" role="progressbar" aria-valuenow="<?php echo $done ?>" aria-valuemin="0" aria-valuemax="100" style="width:'+out.done+'%"> '+out.done+'% </div></div>');
						if (typeof window[name] == 'undefined') {
							window[name] = '';
							if (type == 'html') {
								window[name] = htmlOpen(title);
							}
						}
						window[name] += out.data;
						if (type != 'html') {
							if (pXls.tot.rows < 1000000) {
								out.data.split('"\n').forEach(line => {
									row = [];
									line += '"';
									line.split('","').forEach(col => {
										col = col.replace(/^"/, '');
										col = col.replace(/"$/, '');
										col = col.replace(/\r/g, '');
										col = col.replace(/_x000d_/g, '');
										col = col.replace(/""/g, '"');
										col = col.replace(/\\"/g, '"');
										if (col) {
											if(isNumeric(col)) {
												if (col.length < 11) {
													if (col[0]!='0' || col == '0') {
														col=Number(col);
													}
												}
											}
											row.push(col)
										}
									});
									if (row.length > 0) {
										if (pXls.tot.header == 0) {
											pXls.tot.header++;
											pXls.data.header.push(row);
										}else{
											pXls.tot.rows++;
											pXls.data.body.push(row);
										}
									}
								})
								ext = "xlsx";
							}else{
								ext = "csv";
							}
						}
						if (out.done < 100) {
							peaExtract(url, ++page, modal, body, name, title, type);
						}else{
							if (type=='html') {
								window[name] += htmlClose();
								ext = 'html';
							}else{
								if (ext == "xlsx" && typeof XLSX == "undefined") {
									ext = "csv";
								}
							}
							if (ext == "xlsx") {
								/* generate worksheet and workbook */
								const worksheet = XLSX.utils.json_to_sheet(pXls.data.body);
								const workbook = XLSX.utils.book_new();
								XLSX.utils.book_append_sheet(workbook, worksheet, "Sheet 1");

								/* fix headers */
								XLSX.utils.sheet_add_aoa(worksheet, pXls.data.header, { origin: "A1" });

								/* calculate column width */
								/* const max_width = rows.reduce((w, r) => Math.max(w, r.name.length), 10); */
								/* worksheet["!cols"] = [ { wch: max_width } ]; */

								/* create an XLSX file and try to save to file */
								XLSX.writeFile(workbook, title+'.'+ext, { compression: true });
							}else{
								var blob = new Blob([window[name]], { type: 'text/'+ext });
								var href = window.URL.createObjectURL(blob);
								var b = document.createElement('a');
								b.setAttribute('href', href);
								b.setAttribute('download', title+'.'+ext);
								b.click();
							}
							modal.modal("hide");
							delete window[name];
						}
					}
				}else{
					delete window[name];
				}
			}
		});
	};
});
