<html>
    <head>
        <script src='js/jquery-1.8.2.min.js' type='text/javascript' language='javascript'></script>	
        <script src='js/jquery-ui-1.9.2.custom.min.js' type='text/javascript' language='javascript'></script>
        <script src='js/libs/supercore.js' type='text/javascript' language='javascript'></script>	
        <script src='js/main.js' type='text/javascript' language='javascript'></script>	
        
        <link type="text/css" href="css/main.css" rel="stylesheet">
        <link type="text/css" href="css/jquery-ui.css" rel="stylesheet">
    </head>		
    <body>
        <script>
            <?php echo $script ?>
        </script>
        <div id="headerBox"><?php echo $headernav; ?></div>
        <div id="sidebarBox"><?php echo $sidebar; ?></div>
        <div id="sidebarBox_2"></div>
        <div id="contentBox"><?php echo $content; ?></div>
        <div id="contentBox_2"></div>
    </body>
</html>