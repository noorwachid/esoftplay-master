Array.prototype.inArray = function (a, b) {
  var i;
  for (i = 0; i < this.length; i++) {
    if (b) {
      if (this[i].toLowerCase() == a.toLowerCase()) {
        return true
      }
    } else { if (this[i] == a) {
        return true
      }
    }
  }
  return false
};
var dcTime = 250;
var dcDelay = 100;
var dcAt = 0;
var savEvent = null;
var savEvtTime = 0;
var savTO = null;
var linkElem = null;
function hadDoubleClick() {
  var d = new Date();
  var a = d.getTime();
  if ((a - dcAt) < dcDelay) {
    return true
  }
  return false
};
function enablePreview(g, h) {
  $(g).each(function () {
    $(this).click(function () {
      var e = getNum(this.id);
      var f = files[e].path;
      if (hadDoubleClick()) {
        return false
      } else {
        linkElem = $('#a' + e).get(0)
      }
      d = new Date();
      savEvtTime = d.getTime();
      savTO = setTimeout(function () {
        if (savEvtTime - dcAt > 0) {
          var a = getFileExtension(f);
          var b = supporedPreviewExts.split(",");
          var c = false;
          for (i in b) {
            var d = typeof(b[i]);
            if (d.toLowerCase() == 'string' && b[i].toLowerCase() == a.toLowerCase()) {
              c = true;
              break
            }
          }
          if (c) {
            switch (files[e].cssClass) {
            case 'fileVideo':
            case 'fileMusic':
            case 'fileFlash':
              $('#playGround').html('<a id="playGround' + e + '" href="' + files[e].path + '"><div id="player">&nbsp;this is mine</div></a> ');
              $('#playGround' + e).html('');
              $('#playGround' + e).media({
                width: 255,
                height: 210,
                autoplay: true
              });
              showThickBox($('#a' + e).get(0), appendQueryString('#TB_inline', 'height=250' + '&width=256' + '&inlineId=winPlay&modal=true'));
              break;
            default:
              showThickBox(linkElem, appendQueryString(f, 'KeepThis=true&TB_iframe=true&height=' + thickbox.height + '&width=' + thickbox.width))
            }
          }
        }
        return false
      },
      dcTime);
      return false
    });
    $(this).dblclick(function () {
      var d = new Date();
      dcAt = d.getTime();
      if (savTO != null) {
        clearTimeout(savTO);
        savTO = null
      }
      if (typeof(selectFile) != 'undefined') {
        selectFile(files[h].url)
      } else generateDownloadIframe(appendQueryString(getUrl('download'), 'path=' + files[h].path, ['path'])); {}
    })
  })
};
function tableRuler(a) {
  var b = $(a);
  $(b).each(function () {
    $(this).mouseover(function () {
      $(this).addClass('over')
    });
    $(this).mouseout(function () {
      $(this).removeClass('over')
    })
  })
};
function previewMedia(a) {
  $('#preview' + a).html('');
  $('#preview' + a).media({
    width: 255,
    height: 210,
    autoplay: true
  });
  return false
};
function getFileExtension(a) {
  if (a.length == 0) return "";
  var b = a.lastIndexOf(".");
  if (b == -1) return "";
  var c = a.substr(b + 1, a.length);
  return c
};
function closeWindow() {
  if (window.confirm(warningCloseWindow)) {
    window.close()
  }
  return false
};
function getUrl(a, b, c, d) {
  var e = '';
  var f = new Array();
  if (typeof(b) == 'boolean' && b) {
    var g = document.getElementById('limit');
    var h = typeof(g);
    if (h != 'undefined' && g) {
      f[f.length] = 'limit';
      e += (e == '' ? '' : '&') + 'limit=' + g.options[g.selectedIndex].value
    }
  }
  if (typeof(c) == 'boolean' && c) {
    e += (e == '' ? '' : '&') + 'view=' + getView();
    f[f.length] = 'view'
  }
  if (typeof(d) == 'boolean' && d && searchRequired) {
    var i = 0;
    $('input[@name=search_recursively][@checked]').each(function () {
      i = this.value
    });
    var j = document.getElementById('search_folder');
    e += (e == '' ? '' : '&') + 'search=1&search_name=' + $('#search_name').val() + '&search_recursively=' + i + '&search_mtime_from=' + $('#search_mtime_from').val() + '&search_mtime_to=' + $('#search_mtime_to').val() + '&search_folder=' + j.options[j.selectedIndex].value;
    f[f.length] = 'search';
    f[f.length] = 'search_recursively';
    f[f.length] = 'search_mtime_from';
    f[f.length] = 'search_mtime_to';
    f[f.length] = 'search_folder';
    f[f.length] = 'search_name';
    f[f.length] = 'search'
  }
  return appendQueryString(appendQueryString(urls[a], queryString), e, f)
};
function changeView() {
  var a = getUrl('view', true, true);
  $('#rightCol').empty();
  ajaxStart('#rightCol');
  $('#rightCol').load(a, {},
  function () {
    ajaxStop('#rightCol img.ajaxLoadingImg');
    urls.present = getUrl('home', true, true);
    initAfterListingLoaded()
  })
};
function goParentFolder() {
  searchRequired = false;
  var a = appendQueryString(getUrl('view', true, true), 'path=' + parentFolder.path, ['path']);
  $('#rightCol').empty();
  ajaxStart('#rightCol');
  $('#rightCol').load(a, {},
  function () {
    urls.present = appendQueryString(getUrl('home', true, true), 'path=' + parentFolder.path, ['path']);
    ajaxStop('#rightCol img.ajaxLoadingImg');
    initAfterListingLoaded()
  })
};
function appendQueryString(a, b, c) {
  if (typeof(c) == 'object' && c.length) {
    var d = false;
    var e = a.split("?");
    a = e[0];
    var f = 1;
    if (typeof(e[1]) != 'undefined' && e[1] != '') {
      var g = e[1].split("&");
      for (var i = 0; i < g.length; i++) {
        var h = g[i].split('=');
        for (var j = 0; j < c.length; j++) {
          if (h[0] == c[j]) {
            d = true
          }
        }
        if (!d) {
          a += ((f == 1 ? '?' : '&') + h[0] + '=' + h[1]);
          f++
        }
      }
    }
  }
  if (b != '') {
    a = (a.indexOf('?') > -1 ? a + '&' + b : a + '?' + b)
  }
  return a.replace((new RegExp(CONFIG_SYS_DEFAULT_PATH, 'g')), '')
};
function initAfterListingLoaded() {
  parsePagination();
  parseCurrentFolder();
  var a = getView();
  setDocInfo('root');
  if (a != '') {
    switch (a) {
    case 'thumbnail':
      enableContextMenu('dl.thumbnailListing');
      for (i in files) {
        if (files[i].type == 'folder') {
          enableFolderBrowsable(i)
        } else {
          switch (files[i].cssClass) {
          case 'filePicture':
            break;
          case 'fileFlash':
            break;
          case 'fileVideo':
            break;
          case 'fileMusic':
            break;
          default:
          }
          enablePreview('#dt' + i, i);
          enablePreview('#thumbUrl' + i, i);
          enablePreview('#a' + i, i)
        }
        enableShowDocInfo(i)
      }
      break;
    case 'detail':
    default:
      enableContextMenu('#fileList tr');
      for (i in files) {
        if (files[i].type == 'folder') {
          enableFolderBrowsable(i)
        } else {
          switch (files[i].cssClass) {
          case 'filePicture':
            $('#row' + i + ' td a').attr('rel', 'ajaxphotos');
            break;
          case 'fileFlash':
            break;
          case 'fileVideo':
            break;
          case 'fileMusic':
            break;
          default:
          };
          enablePreview('#row' + i + ' td a', i)
        }
        enableShowDocInfo(i)
      }
      break
    }
  }
};
function enableFolderBrowsable(a, b) {
  switch (getView()) {
  case 'thumbnail':
    $('#dt' + a + ' , #dd' + a + ' a').each(function () {
      doEnableFolderBrowsable(this, a)
    });
    break;
  case 'detail':
  default:
    $('#row' + a + ' td a').each(function () {
      doEnableFolderBrowsable(this, a)
    })
  }
};
function doEnableFolderBrowsable(d, e) {
  $(d).click(function () {
    {
      searchRequired = false;
      var a = typeof(e);
      if (a.toUpperCase() == 'STRING') {
        var b = (e.indexOf(urls.view) >= 0 ? e : files[e].path)
      } else {
        var b = files[e].path
      }
      var c = appendQueryString(getUrl('view', true, true), 'path=' + b, ['path']);
      $('#rightCol').empty();
      ajaxStart('#rightCol');
      $('#rightCol').load(c, {},
      function () {
        urls.present = appendQueryString(getUrl('home', true, true), 'path=' + b, ['path']);
        ajaxStop('#rightCol img.ajaxLoadingImg');
        initAfterListingLoaded()
      })
    };
    return false
  })
};
function ajaxStart(a, b, c) {
  if (typeof(c) == 'undefined') {
    c = '#ajaxLoading img'
  }
  if (typeof(b) != 'undefined') {
    $(c).clone().attr('id', b).appendTo(a)
  } else {
    $(c).clone(true).appendTo(a)
  }
};
function ajaxStop(a) {
  $(a).remove()
};
function changePaginationLimit(a) {
  var b = getUrl('view', true, true, true);
  $('#rightCol').empty();
  ajaxStart('#rightCol');
  $('#rightCol').load(b, {},
  function () {
    urls.present = appendQueryString(getUrl('home', true, true), 'path=' + parentFolder.path, ['path']);
    ajaxStop('#rightCol img.ajaxLoadingImg');
    initAfterListingLoaded()
  })
};
function getUrlVarValue(a, b) {
  if (a != '' && b != '') {
    var c = a.split("?");
    baseUrl = c[0];
    var d = 1;
    if (typeof(c[1]) != 'undefined' && c[1] != '') {
      var e = c[1].split("&");
      for (var i = 0; i < e.length; i++) {
        var f = e[i].split('=');
        if (f[0] == b) {
          return f[1]
        }
      }
    }
  }
  return ''
};
function parseCurrentFolder() {
  var a = currentFolder.friendly_path.split('/');
  var b = '';
  var c = getUrl('view', true, true);
  var d = '';
  for (var i = 0; i < a.length; i++) {
    if (i == 0) {
      d += paths.root;
      b += '/<a href="' + appendQueryString(c, 'path=' + d, ['path']) + '"><span class="folderRoot">' + paths.root_title + '</span></a>'
    } else { if (a[i] != '') {
        d += a[i] + '/';
        b += '/<a href="' + appendQueryString(c, 'path=' + d, ['path']) + '"><span class="folderSub">' + a[i] + '</span></a>'
      }
    }
  }
  $('#currentFolderPath').empty().append(b);
  $('#currentFolderPath a').each(function () {
    doEnableFolderBrowsable(this, $(this).attr('href'))
  })
};
function parsePagination() {
  $('p.pagination a[@id!=pagination_parent_link]').each(function () {
    $(this).click(function () {
      var a = getUrlVarValue($(this).attr('href'), 'page');
      var b = appendQueryString(getUrl('view', true, true, searchRequired), 'page=' + a, ['page']);
      $('#rightCol').empty();
      ajaxStart('#rightCol');
      $('#rightCol').load(b, {},
      function () {
        urls.present = appendQueryString(getUrl('home', true, true, searchRequired), 'page=' + a, ['page']);
        ajaxStop('#rightCol img.ajaxLoadingImg');
        initAfterListingLoaded()
      });
      return false
    })
  })
};
function getView() {
  var a = $('input[@name=view][@checked]').get(0);
  if (typeof(a) != 'undefined') {
    return a.value
  } else {
    return ''
  }
};
function getNum(a) {
  if (typeof(a) != 'undefined' && a != '') {
    var r = a.match(/[\d\.]+/g);
    if (typeof(r) != 'undefined' && r && typeof(r[0]) != 'undefined') {
      return r[0]
    }
  }
  return 0
};
function enableContextMenu(j) {
  $(j).contextMenu('contextMenu', {
    bindings: {
      'menuSelect': function (t) {
        var a = (getNum($(t).attr('id')));
        selectFile(files[a].url)
      },
      'menuPlay': function (t) {
        var a = (getNum($(t).attr('id')));
        $('#playGround').html('<a id="playGround' + a + '" href="' + files[a].path + '"><div id="player">&nbsp;this is mine</div></a> ');
        $('#playGround' + a).html('');
        $('#playGround' + a).media({
          width: 255,
          height: 210,
          autoplay: true
        });
        showThickBox($('#a' + a).get(0), appendQueryString('#TB_inline', 'height=250' + '&width=258' + '&inlineId=winPlay&modal=true'))
      },
      'menuPreview': function (t) {
        var a = (getNum($(t).attr('id')));
        $('#a' + a).click()
      },
      'menuDownload': function (t) {
        var a = (getNum($(t).attr('id')));
        generateDownloadIframe(appendQueryString(getUrl('download', false, false), 'path=' + files[a].path, ['path']))
      },
      'menuRename': function (t) {
        var a = (getNum($(t).attr('id')));
        showThickBox($('#a' + a).get(0), appendQueryString('#TB_inline', 'height=100' + '&width=350' + '&inlineId=winRename&modal=true'));
        $('div#TB_window #renameName').val(files[a].name);
        $('div#TB_window #original_path').val(files[a].path);
        $('div#TB_window #renameNum').val(a)
      },
      'menuEdit': function (t) {
        var a = (getNum($(t).attr('id')));
        var b = '';
        switch (files[a].cssClass) {
        case 'filePicture':
          b = getUrl('image_editor');
          break;
        default:
          b = getUrl('text_editor')
        }
        var c = "status=yes,menubar=no,resizable=yes,scrollbars=yes,location=no,toolbar=no";
        c += ",height=" + screen.height + ",width=" + screen.width;
        if (typeof(window.screenX) != 'undefined') {
          c += ",screenX = 0,screenY=0"
        } else if (typeof(window.screenTop) != 'undefined') {
          c += ",left = 0,top=0"
        }
        var d = window.open(b + ((b.lastIndexOf("?") > -1) ? "&" : "?") + "path=" + files[a].path, '', c);
        d.focus()
      },
      'menuCut': function (t) {},
      'menuCopy': function (t) {},
      'menuPaste': function (t) {},
      'menuDelete': function (t) {
        var b = (getNum($(t).attr('id')));
        if (window.confirm(warningDelete)) {
          $.getJSON(appendQueryString(getUrl('delete', false, false), 'delete=' + files[b].path, ['delete']), function (a) {
            if (typeof(a.error) == 'undefined') {
              alert('Unexpected Error.')
            } else if (a.error != '') {
              alert(a.error)
            } else {
              switch (getView()) {
              case 'thumbnail':
                $('#dl' + b).remove();
                break;
              case 'detail':
              default:
                $('#row' + b).remove()
              }
              files[b] = null
            }
          })
        }
      }
    },
    onContextMenu: function (a) {
      return true
    },
    onShowMenu: function (a, b) {
      switch (getView()) {
      case 'thumbnail':
        var c = getNum(a.target.id);
        break;
      case 'detail':
      default:
        switch (a.target.tagName.toLowerCase()) {
        case 'span':
          if ($(a.target).parent().get(0).tagName.toLowerCase() == 'a') {
            var c = getNum($(a.target).parent().parent().parent().attr('id'))
          } else {
            var c = getNum($(a.target).parent().parent().parent().parent().attr('id'))
          }
          break;
        case 'td':
          var c = getNum($(a.target).parent().attr('id'));
          break;
        case 'a':
        case 'input':
          var c = getNum($(a.target).parent().parent().attr('id'));
          break
        }
      }
      var d = new Array;
      if (typeof(selectFile) == 'undefined') {
        d[d.length] = '#menuSelect'
      }
      d[d.length] = '#menuCut';
      d[d.length] = '#menuCopy';
      d[d.length] = '#menuPaste';
      switch (files[c].type) {
      case 'folder':
        if (numFiles < 1) {
          d[d.length] = '#menuPaste'
        }
        d[d.length] = '#menuPreview';
        d[d.length] = '#menuDownload';
        d[d.length] = '#menuEdit';
        d[d.length] = '#menuPlay';
        d[d.length] = '#menuDownload';
        break;
      default:
        var e = false;
        if (permits.edit) {
          var f = getFileExtension(files[c].path);
          var g = supporedPreviewExts.split(",");
          for (var i = 0; i < g.length; i++) {
            if (typeof(g[i]) != 'undefined' && typeof(g[i]).toLowerCase() == 'string' && g[i].toLowerCase() == f.toLowerCase()) {
              e = true;
              break
            }
          }
        }
        if (!e || permits.view_only) {
          d[d.length] = '#menuEdit'
        }
        switch (files[c].cssClass) {
        case 'filePicture':
          d[d.length] = '#menuPlay';
          break;
        case 'fileCode':
          d[d.length] = '#menuPlay';
          break;
        case 'fileVideo':
        case 'fileFlash':
        case 'fileMusic':
          d[d.length] = '#menuPreview';
          d[d.length] = '#menuEdit';
          break;
        default:
          d[d.length] = '#menuPreview';
          d[d.length] = '#menuPlay'
        }
        d[d.length] = '#menuPaste'
      }
      if (!permits.edit || permits.view_only) {
        d[d.length] = '#menuEdit'
      }
      if (!permits.del || permits.view_only) {
        d[d.length] = '#menuDelete'
      }
      if (!permits.cut || permits.view_only) {
        d[d.length] = '#menuCut'
      }
      if (!permits.copy || permits.view_only) {
        d[d.length] = '#menuCopy'
      }
      if ((!permits.cut && !permits.copy) || permits.view_only) {
        d[d.length] = '#menuPaste'
      }
      if (!permits.rename || permits.view_only) {
        d[d.length] = '#menuRename'
      }
      var h = '';
      for (var t in b) {}
      $(b).children().children().children().each(function () {
        if (d.inArray('#' + this.id)) {
          $(this).parent().remove()
        }
      });
      return b
    }
  })
};
var fileUploadElemIds = new Array();
function addMoreFile() {
  var a = $($('div#TB_window #fileUploadBody  tr').get(0)).clone();
  do {
    var b = 'upload' + generateUniqueId(10)
  } while (fileUploadElemIds.inArray(b));
  fileUploadElemIds[fileUploadElemIds.length] = b;
  $(a).appendTo('div#TB_window #fileUploadBody');
  $('input[@type=file]', a).attr('id', b);
  $('span.uploadProcessing', a).attr('id', 'ajax' + b);
  $('a.buttonLink', a).click(function () {
    uploadFile(b)
  });
  $('a.action', a).show().click(function () {
    cancelFileUpload(b)
  });
  $(a).show();
  return false
};
function cancelFileUpload(a) {
  $('div#TB_window #' + a).parent().parent().remove();
  while ($('div#TB_window #fileUploadBody tr').length < 2) {
    addMoreFile()
  }
  return false
};
function uploadFile(c) {
  var d = getFileExtension($('#' + c).val());
  if (d == '') {
    alert(noFileSelected);
    return false
  }
  var f = supportedUploadExts.split(",");
  var g = false;
  for (i in f) {
    if (typeof(f[i]) == 'string') {
      g = true;
      break
    }
  }
  if (!g) {
    alert(msgInvalidExt);
    return false
  }
  $('#ajax' + c).hide();
  $('#ajax' + c).show();
  $.ajaxFileUpload({
    url: appendQueryString(getUrl('upload', false, false), 'folder=' + currentFolder.path, ['folder']),
    secureuri: false,
    fileElementId: c,
    dataType: 'json',
    success: function (a, b) {
      if (typeof(a.error) != 'undefined') {
        if (a.error != '') {
          alert(a.error);
          $('#ajax' + c).hide()
        } else {
          cancelFileUpload(c);
          numRows++;
          files[numRows] = {};
          for (var i in a) {
            if (i != 'error') {
              if (i == 'path') {
                a[i] = a[i].replace(CONFIG_SYS_DEFAULT_PATH, '')
              };
              files[numRows][i] = a[i]
            }
          }
          addDocumentHtml(numRows)
        }
      }
    },
    error: function (a, b, e) {
      $('#ajax' + c).hide();
      alert(e)
    }
  });
  return false
};
function generateUniqueId(a) {
  var b = a || 32;
  var c = "0123456789abcdefghijklmnopqurstuvwxyzABCDEFGHIJKLMNOPQURSTUVWXYZ";
  var d = '';
  for (var i = 0; i <= b; i++) {
    d += c.substr(Math.floor(Math.random() * 62), 1)
  }
  return (d)
};
function generateDownloadIframe(a) {
  var b = 'ajaxDownloadIframe';
  $('#' + b).remove();
  var c = '<iframe id="' + b + '" name="' + b + '" style="position:absolute; top:-9999px; left:-9999px" src="' + a + '" ';
  c += '/>';
  $(c).appendTo(document.body)
};
function showThickBox(b, c) {
  $(b).attr('href', c);
  var t = b.title || b.name || null;
  var a = b.href || b.alt;
  var g = b.rel || false;
  tb_show(t, a, g);
  b.blur();
  return false
};
function uploadFileWin(a) {
  showThickBox(a, appendQueryString('#TB_inline', 'height=200' + '&width=450' + '&inlineId=winUpload&modal=true'));
  if ($('#fileUploadBody tr').length <= 1) {
    addMoreFile()
  }
  return false
};
function newFolderWin(a) {
  showThickBox(a, appendQueryString('#TB_inline', 'height=100' + '&width=250' + '&inlineId=winNewFolder&modal=true'));
  $('#new_folder').val('');
  return false
};
function doCreateFolder() {
  $('div#TB_window  #currentNewfolderPath').val(currentFolder.path);
  var c = /^[A-Za-z0-9_ \-]+$/i;
  var d = $('div#TB_window #new_folder');
  if (!c.test($(d).val())) {
    alert(msgInvalidFolderName)
  } else {
    var f = {
      dataType: 'json',
      url: getUrl('create_folder'),
      error: function (a, b, e) {
        alert(e)
      },
      success: function (a) {
        if (a.error != '') {
          alert(a.error)
        } else {
          numRows++;
          files[numRows] = {};
          for (var i in a) {
            if (i != 'error') {
              files[numRows][i] = a[i]
            }
          }
          files[numRows]['path'] = files[numRows]['path'].replace(CONFIG_SYS_DEFAULT_PATH, '');
          addDocumentHtml(numRows);
          tb_remove()
        }
      }
    };
    $('div#TB_window  #formNewFolder').ajaxSubmit(f)
  }
  return false
};
function deleteDocuments() {
  if (!window.confirm(warningDel)) {
    return false
  }
  switch (getView()) {
  case 'thumbnail':
    var c = $('#rightCol dl.thumbnailListing input[@type=checkbox][@checked]');
    break;
  case 'detail':
  default:
    var c = $('#fileList input[@type=checkbox][@checked]')
  }
  var d = document.getElementById('selectedDoc');
  var f;
  var g = false;
  $(d).removeOption(/./);
  $(c).each(function (i) {
    $(d).addOption($(this).val(), getNum($(this).attr('id')), true);
    g = true
  });
  if (g) {
    var h = {
      dataType: 'json',
      url: getUrl('delete'),
      error: function (a, b, e) {
        alert(e)
      },
      success: function (a) {
        if (typeof(a.error) == 'undefined') {
          alert('Unexpected error.')
        } else if (a.error != '') {
          alert(a.error)
        } else {
          for (var i = 0; i < d.options.length; i++) {
            switch (getView()) {
            case 'thumbnail':
              $('#dl' + d.options[i].text).remove();
              break;
            case 'detail':
            default:
              $('#row' + d.options[i].text).remove()
            }
          }
        }
      }
    };
    $('#formAction').ajaxSubmit(h)
  }
  return false
};
function doRename() {
  var c = $('div#TB_window #renameNum').val();
  if (files[c].fileType == 'folder') {
    var d = /^[A-Za-z0-9_ \-]+$/i
  } else {
    var d = /^[A-Za-z0-9_ \-\.]+$/i
  }
  if (!d.test($('div#TB_window  #renameName').val())) {
    if (files[c].fileType == 'folder') {
      alert(msgInvalidFolderName)
    } else {
      alert(msgInvalidFileName)
    }
  } else {
    var f = {
      dataType: 'json',
      url: getUrl('rename'),
      error: function (a, b, e) {
        alert(e)
      },
      success: function (a) {
        if (a.error != '') {
          alert(a.error)
        } else {
          var b = '';
          for (var i in a) {
            if (i != 'error') {
              files[c][i] = a[i]
            }
          }
          files[c].path = files[c].path.replace(CONFIG_SYS_DEFAULT_PATH, '');
          switch (getView()) {
          case 'thumbnail':
            $('#thumbUrl' + c).attr('href', files[c].path);
            $('#thumbImg' + c).attr('src', appendQueryString(getUrl('thumbnail'), 'path=' + files[c].path, ['path']));
            $('#cb' + c).val(files[c].path);
            $('#a' + c).attr('href', files[c].path).text(files[c].name).attr("title", files[c].name);
            break;
          case 'detail':
          default:
            $('#check' + c).val(files[c].path);
            $('#a' + c).attr('href', files[c].path);
            $('#tdnd' + c).text(files[c].name);
            $('#tdth' + c).text(files[c].mtime)
          }
          tb_remove()
        }
      }
    };
    $('div#TB_window #formRename').ajaxSubmit(f)
  }
};
function windowRefresh() {
  document.location.reload();
  return false
};
function infoWin(a) {
  showThickBox(a, appendQueryString('#TB_inline', 'height=180' + '&width=500' + '&inlineId=winInfo&modal=true'));
  return false
};
function checkAll(a) {
  if ($(a).attr('class') == "check_all") {
    $('#tickAll, #actionSelectAll').attr('class', 'uncheck_all');
    $('#tickAll, #actionSelectAll').attr('title', unselectAllText);
    $('#actionSelectAll span').html(unselectAllText);
    switch (getView()) {
    case 'thumbnail':
      $('#rightCol dl.thumbnailListing input[@type=checkbox]').each(function (i) {
        $(this).attr("checked", 'checked')
      });
      break;
    case 'detail':
    default:
      $("#fileList tr[@id^=row] input[@type=checkbox]").each(function (i) {
        $(this).attr("checked", 'checked')
      })
    }
  } else {
    $('#tickAll, #actionSelectAll').attr('class', 'check_all');
    $('#tickAll, #actionSelectAll').attr('title', selectAllText);
    $('#actionSelectAll span').html(selectAllText);
    switch (getView()) {
    case 'thumbnail':
      $('#rightCol dl.thumbnailListing input[@type=checkbox]').each(function (i) {
        $(this).removeAttr("checked")
      });
      break;
    case 'detail':
    default:
      $("#fileList tr[@id^=row] input[@type=checkbox]").each(function (i) {
        $(this).removeAttr("checked")
      })
    }
  }
  return false
};
function cutDocuments(a) {
  repositionDocuments(a, getUrl('cut'), 'cut');
  return false
};
function copyDocuments(a) {
  repositionDocuments(a, getUrl('copy'), 'copy');
  return false
};
function repositionDocuments(c, d, f) {
  switch (getView()) {
  case 'thumbnail':
    var g = $('#rightCol dl.thumbnailListing input[@type=checkbox][@checked]');
    break;
  case 'detail':
  default:
    var g = $('#fileList input[@type=checkbox][@checked]')
  }
  var h = document.getElementById('selectedDoc');
  var j;
  var k = false;
  $(h).removeOption(/./);
  $(g).each(function (i) {
    $(h).addOption($(this).val(), getNum($(this).attr('id')), true);
    k = true
  });
  if (!k) {
    alert(c)
  } else {
    var l = document.formAction;
    var m = $('#action_value');
    l.action = d;
    $('#currentFolderPathVal').val(currentFolder.path);
    $(m).val(f);
    var n = {
      dataType: 'json',
      error: function (a, b, e) {
        alert(e)
      },
      success: function (a) {
        if (typeof(a.error) == 'undefined') {
          alert('Unexpected Error')
        } else if (a.error != '') {
          alert(a.error)
        } else {
          numFiles = parseInt(a.num);
          var b = (f == 'copy' ? 'copyFlag' : 'cutFlag');
          action = f;
          for (var i = 1; i < numRows; i++) {
            $('#flag' + i).attr('class', 'noFlag')
          }
          for (var i = 0; i < h.options.length; i++) {
            $('#flag' + h.options[i].text).attr('class', b)
          }
        }
      }
    };
    $(l).ajaxSubmit(n)
  }
  return false
};
function pasteDocuments(b) {
  if (numFiles) {
    var c = (action == 'copy' ? warningCopyPaste : warningCutPaste);
    if (window.confirm(c)) {
      $.getJSON(appendQueryString(getUrl('paste'), 'current_folder_path=' + currentFolder.path, ['current_folder_path']), function (a) {
        if (typeof(a.error) == 'undefined') {
          alert('Unexpected Error.')
        } {
          if (a.error != '') {
            alert(a.error)
          }
          for (var j in a.files) {
            numRows++;
            files[numRows] = {};
            for (var i in a.files[j]) {
              files[numRows][i] = a.files[j][i]
            }
            addDocumentHtml(numRows)
          }
          numFiles = parseInt(a.unmoved_files)
        }
      })
    }
  } else {
    alert(b)
  }
  return false
};
function addDocumentHtml(a) {
  var b = "";
  if (!files[a].is_writable) {
    b = "disabled"
  }
  switch (getView()) {
  case 'thumbnail':
    $('<dl class="thumbnailListing" id="dl' + a + '" ><dt id="dt' + a + '" class="' + files[a].cssClass + '"></dt><dd id="dd' + a + '" class="thumbnailListing_info"><span id="flag' + a + '" class="' + files[a].flag + '">&nbsp;</span><input id="cb' + a + '" type="checkbox"  class="radio" ' + b + ' name="check[]" class="input" value="' + files[a].path + '" /><a href="' + files[a].path + '" title="' + files[a].name + '" id="a' + a + '">' + (typeof(files[a].short_name) != 'undefined' ? files[a].short_name : files[a].name) + '</a></dd></dl>').appendTo('#content');
    if (files[a].type == 'folder') {
      enableFolderBrowsable(a)
    } else {
      switch (files[a].cssClass) {
      case 'filePicture':
        $('<a id="thumbUrl' + a + '" rel="thumbPhotos" href="' + files[a].path + '"><img src="' + appendQueryString(getUrl('thumbnail', false, false), 'path=' + encodeURIComponent(files[a].path), ['path']) + '" id="thumbImg' + a + '"></a>').appendTo('#dt' + a);
        break;
      case 'fileFlash':
        break;
      case 'fileVideo':
        break;
      case 'fileMusic':
        break;
      default:
      }
      enablePreview('#dl' + a + ' a', [a])
    }
    enableContextMenu('#dl' + a);
    enableShowDocInfo(a);
    break;
  case 'detail':
  default:
    var c = (a % 2 ? "even" : "odd");
    $('<tr class="' + c + '" id="row' + a + '"><td id="tdz' + a + '" align="center"><span id="flag' + a + '" class="' + files[a].flag + '">&nbsp;</span><input type="checkbox" class="radio" name="check[]" id="cb' + a + '" value="' + files[a].path + '" ' + b + ' /></td><td align="center" class="fileColumns"   id="tdst1">&nbsp;<a id="a' + a + '" href="' + files[a].path + '"><span class="' + files[a].cssClass + '">&nbsp;</span></a></td><td class="left docName" id="tdnd' + a + '"><a id="a' + a + '" href="' + files[a].path + '">' + (typeof(files[a].short_name) != 'undefined' ? files[a].short_name : files[a].name) + '</a>' + '</td><td class="docInfo" id="tdrd' + a + '">' + files[a].size + '</td><td class="docInfo" id="tdth' + a + '">' + files[a].mtime + '</td></tr>').appendTo('#fileList');
    if (files[a].type == 'folder') {
      enableFolderBrowsable(a)
    } else {
      switch (files[a].cssClass) {
      case 'filePicture':
        break;
      case 'fileFlash':
        break;
      case 'fileVideo':
        break;
      case 'fileMusic':
        break;
      default:
      }
      enablePreview('#row' + a + ' td a', a)
    }
    enableContextMenu('#row' + a);
    enableShowDocInfo(a);
    break
  }
};
function enableShowDocInfo(a) {
  $('#cb' + a).click(function () {
    setDocInfo('doc', a)
  })
};
function setDocInfo(a, b) {
  var c = {};
  if (a == 'root') {
    c = currentFolder
  } else {
    c = files[b]
  }
  if (c.type == "folder") {
    $('#folderPath').text(c.name);
    $('#folderFile').text(c.file);
    $('#folderSubdir').text(c.subdir);
    $('#folderCtime').text(c.ctime);
    $('#folderMtime').text(c.mtime);
    if (c.is_readable == '1') {
      $('#folderReadable').html("<span class=\"flagYes\">&nbsp;</span>")
    } else {
      $('#folderReadable').html("<span class=\"flagNo\">&nbsp;</span>")
    }
    if (c.is_writable == '1') {
      $('#folderWritable').html("<span class=\"flagYes\">&nbsp;</span>")
    } else {
      $('#folderWritable').html("<span class=\"flagNo\">&nbsp;</span>")
    }
    $('#folderFieldSet').css('display', '');
    $('#fileFieldSet').css('display', 'none')
  } else {
    $('#fileName').text(c.name);
    $('#fileSize').text(c.size + (c.is_image == 1 ? ' (' + c.x + ' X ' + c.y + ')' : ''));
    $('#fileType').text(c.fileType);
    $('#fileCtime').text(c.ctime);
    $('#fileMtime').text(c.mtime);
    if (c.is_readable == '1') {
      $('#fileReadable').html("<span class=\"flagYes\">&nbsp;</span>")
    } else {
      $('#fileReadable').html("<span class=\"flagNo\">&nbsp;</span>")
    }
    if (c.is_writable == '1') {
      $('#fileWritable').html("<span class=\"flagYes\">&nbsp;</span>")
    } else {
      $('#fileWritable').html("<span class=\"flagNo\">&nbsp;</span>")
    }
    $('#folderFieldSet').css('display', 'none');
    $('#fileFieldSet').css('display', '');
    var d = (getView() == 'detail') ? $('#fileList input[@type=checkbox][@checked]').length : $('#content input[@type=checkbox][@checked]').length;
    if (typeof(selectFile) != 'undefined' && (d == 1)) {
      $('#selectCurrentUrl').unbind('click').click(function () {
        selectFile(c.url)
      });
      $('#returnCurrentUrl').show()
    } else {
      $('#returnCurrentUrl').hide()
    }
  }
};
function searchDocuments() {
  searchRequired = true;
  var a = getUrl('view', true, true, true);
  $('#rightCol').empty();
  ajaxStart('#rightCol');
  $('#rightCol').load(a, {},
  function () {
    ajaxStop('#rightCol img.ajaxLoadingImg');
    initAfterListingLoaded()
  });
  return false
};
function closeWinPlay() {
  tb_remove();
  $('#playGround').empty();
  return false
};
function closeWindow(a) {
  if (window.confirm(a)) {
    window.close()
  } else {
    return false
  }
};