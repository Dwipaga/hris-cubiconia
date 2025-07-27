<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Example: Browsing Files</title>
    <script>
        // Helper function to get parameters from the query string.
        function getUrlParam( paramName ) {
            var reParam = new RegExp( '(?:[\?&]|&)' + paramName + '=([^&]+)', 'i' );
            var match = window.location.search.match( reParam );

            return ( match && match.length > 1 ) ? match[1] : null;
        }
        // Simulate user action of selecting a file to be returned to CKEditor.
        function returnFileUrl() {

            var funcNum = getUrlParam( 'CKEditorFuncNum' );
            var fileUrl = '../../../assets/upload/images/banner.jpg';
            window.opener.CKEDITOR.tools.callFunction( funcNum, fileUrl );
            window.close();
        }
    </script>
</head>
<body>
    <?php
        $directory = base_url().'/upload/images/';
        $images = glob($directory . "*.{jpg,JPG,jpeg,JPEG,png,PNG}", GLOB_BRACE);
    ?>
    <?php foreach ($images as $key => $value): ?>
        <button onclick="returnFileUrl()">Select File</button>
    <?php endforeach; ?>
</body>
</html>
