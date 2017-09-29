$(function () {
  tinymce.init({
    selector: 'textarea.editor',
    init_instance_callback: function (editor) {
      editor.on('blur', function (e) {
        editor.save();
      });
    }
  });
});
