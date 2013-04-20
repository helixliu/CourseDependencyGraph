<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Course Dependency Graph </title>
        <link rel='stylesheet' href='css/main_style.css' type='text/css' />
        <script language="javascript" type="text/javascript" src="js/jquery.min.js"></script>
        <script language="javascript" type="text/javascript" src="js/arbor.js"></script>
        <script language="javascript" type="text/javascript" src="js/arbor-tween.js"></script>
        <script language="javascript" type="text/javascript" src="js/renderer.js"></script>
        <script language="javascript" type="text/javascript" src="js/graphics.js"></script>
        <script language="javascript" type="text/javascript" src="js/courseDependencyGraph.js"></script>
    </head>
    
    <body>
        <div id="container">
            <canvas id="viewport" width="800" height="600"></canvas>

           <div id="sidePanel">
               <div id="sidePanelNav">     
                   <a id="clearGraph" href="#">Reset Graph</a> <b>|</b>
                   <a id="displayEntireDependencyGraph" href="#">Display Course Dependency Graph</a>
               </div>
         
               <select id="academicMajor">
                   <option value="json/computerscience.json">Computer Science</option>
                   <option value="json/mathematics.json">Mathematics</option>
                   <option value="json/psychology.json">Psychology</option>
               </select>
                     
               <form id="courses"> 
                   <div id="courseButtons"></div>     
               </form>
           </div>
        </div>
    </body>
</html>
