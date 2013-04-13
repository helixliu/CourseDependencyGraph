<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Computer Science Course Dependency Graph </title>
        <script language="javascript" type="text/javascript" src="js/jquery.min.js"></script>
        <script language="javascript" type="text/javascript" src="js/arbor.js"></script>
        <script language="javascript" type="text/javascript" src="js/arbor-tween.js"></script>
        <script language="javascript" type="text/javascript" src="js/renderer.js"></script>
        <script language="javascript" type="text/javascript" src="js/graphics.js"></script>
        <style type="text/css">
            #viewport
            {
                display:inline;
                float: left;
            }
        </style>
    </head>
    
    <body>
        <div id="container">
            <canvas id="viewport" width="800" height="600"></canvas>

            <div id="sidePanel">
                <form id="courses"> 
                    <input type="button" value="Display Entire Course Dependency Graph" id="displayEntireDependencyGraph" /><br />
                    <input type="button" value="Clear Graph" id="clearGraph" /><br /><br />
                    
                    <label><b>Courses</b></label><br />
                    <!--<input type="button" value="CS111: Introduction to Computer Science" id="CS111" /><br />
                    <input type="button" value="CS112: Data Structures" id="CS112" /><br />-->
      
                    <?php
                        $jsonFileName = "json/computerscience.json";
                        $jsonFileContent = file_get_contents($jsonFileName); //get content of file
                        $jsonData = json_decode($jsonFileContent, true); //converts content in json; return an array
                        //print_r($jsonData); //prints the entire array
                        
                        //iterate through array with key => value
                        foreach($jsonData as $courseCode => $courseObj)
                        {
                            $courseName = $courseObj['Name'];
                            echo "<input type=\"button\" value=\" $courseCode : $courseName\" id=\"$courseCode\" /><br />";
                        }
                        
                        //echo '<br />';
                        //generateJSEventTriggers($jsonData);
                        
                        function generateJSEventTriggers($jsonData)
                        {
                            foreach($jsonData as $courseCode => $courseObj)
                            {
                                echo "document.getElementById('$courseCode').onclick = function(){changeNodeState('$courseCode')};<br />";
                            }
                        }
                    ?>

                </form>
            </div>
        </div>
    
        <script language="javascript" type="text/javascript">             
            $(document).ready(function()
            {
                /* Parameters for the Particle System. 
                 * The Particle System is used to represent the course dependency graph.
                 * Below the parameters of the Particle System constructor.
                 *      repulsion - the force repelling nodes from each other
                 *      stiffness - the rigidity of the edges
                 *      friction - the amount of damping in the system
                 *      gravity - an addtional force attracting nodes to the orgin
                 *      fps - frames per second
                 *      dt - timestep to use for steeping the simulation
                 *      precision - accuracy vs. speed in force calculations
                 */
                var repulsionValue = 2000;
                var stiffnessValue = 600;
                var frictionValue = .5;
                var gravityValue = true;
                var fpsValue = 55;
                var dtValue = 0.02;
                var precisionValue = 0.6;
        
                //call constructor to create the Particle System
                //The user will be able to interact and modify this particle system.
                var particleSystem = arbor.ParticleSystem(
                    {repulsion:repulsionValue, 
                    stiffness:stiffnessValue,
                    friction:frictionValue,
                    gravity:gravityValue,
                    fps:fpsValue, 
                    dt:dtValue,
                    precision:precisionValue
                    }); 
                
                 particleSystem.renderer = Renderer("#viewport"); //Initializes and redraws Particle system
                 
                 //Creation of an Associative Array 
                 //(<Key>:<Value>) Course Code : Course Data
                 //Incoming edges are provided. These are the prerequistes.
                 /*var courseArray = 
                 {
                    "MAT151" : {"Name": "Calculus I for Mathematical and Physical Sciences"},
                    "MAT152" : {"Name": "Calculus II for Mathematical and Physical Sciences", "Prerequisite" : ["MAT151"]},
                    "MAT250" : {"Name": "Introductory Linear Algebra", "Prerequisite" : ["MAT152"]},
                    "CS111" : {"Name": "Introduction to Computer Science"},
                    "CS112" : {"Name": "Data Structures", "Prerequisite" : ["MAT151","CS111"]},
                    "CS205" : {"Name": "Introduction to Discrete Structures I", "Prerequisite" : ["CS111","MAT152"]},
                    "CS206" : {"Name": "Introduction to Discrete Structures II", "Prerequisite" : ["CS205"]},
                    "CS211" : {"Name": "Computer Architecture", "Prerequisite" : ["CS112"]},
                    "CS214" : {"Name": "Systems Programming", "Prerequisite" : ["CS211"]},
                    "CS314" : {"Name": "Principles of Programming Languages", "Prerequisite" : ["CS112","CS205"]},
                    "CS323" : {"Name": "Numerical Analysis and Computing", "Prerequisite" : ["MAT152","MAT250"]},
                    "CS336" : {"Name": "Principles of Information and Data Management", "Prerequisite" : ["CS112"]},
                    "CS344" : {"Name": "Design and Analysis of Computer Algorithms", "Prerequisite" : ["CS112", "CS206"]},
                    "CS352" : {"Name": "Internet Technology", "Prerequisite" : ["CS211","CS206"]},
                    "CS415" : {"Name": "Compilers", "Prerequisite" : ["CS211","CS314"]},
                    "CS416" : {"Name": "Operating Systems Design", "Prerequisite" : ["CS211","CS214"]},
                    "CS417" : {"Name": "Distributed Systems: Concepts and Design", "Prerequisite" : ["CS416"]},
                    "CS419" : {"Name": "Computer Security", "Prerequisite" : ["CS112",["CS416","CS352"]]},
                    "CS428" : {"Name": "Introduction to Computer Graphics", "Prerequisite" : ["CS112","MAT152","MAT250"]},
                    "CS431" : {"Name": "Software Engineering", "Prerequisite" : ["CS112",["CS314","CS336","CS352","CS416"]]},
                    "CS440" : {"Name": "Introduction to Artificial Intelligence", "Prerequisite" : ["CS314"]}
                 };*/
                
                //Possible states of a node
                var UNAVALIABLE_STATE = 0; //Grey Node or Non-Existent Node
                var COMPLETED_STATE = 1; //Green Node
                var READY_STATE = 2; //Yellow Node
                
                var UNAVALIABLE_STATE_COLOR = 'grey';
                var COMPLETED_STATE_COLOR = 'green';
                var READY_STATE_COLOR = 'orange';
                
                var numToColorMapping = {};
                numToColorMapping[UNAVALIABLE_STATE] =  UNAVALIABLE_STATE_COLOR;
                numToColorMapping[COMPLETED_STATE] = COMPLETED_STATE_COLOR;
                numToColorMapping[READY_STATE] = READY_STATE_COLOR;   
                
                //Data on the completed Course Dependency Graph and the course dependency graph that is being built.
                var courseArray; //stores the json data from file of the completed course dependency graph
                var outgoingEdgeGraphArray = {}; //{} = new Object(); contain the outgoing edges of each node of the completed graph
                var nodeStateArray = {}; //keeps track of the current state of each node in the graph that is being bulit

                $.ajax({
                    url: 'json/computerscience.json', //url: path to json file
                    async: false,  //async: function gets called in sequence with code, so var courseArray is populated
                    dataType: 'json', //json data 
                    success: function (json) {courseArray=json;} //sets courseArray with json data
                });
                //console.log(courseArray);
               
                determineAllOutgoingEdges(); //populates the outgoingEdgeGraphArray
                initializeGraph(); //initialize the default state of the Graph; Add courses with no dependency
                
                //Trigger Event buttons (Generated by PHP script)
                //TODO: need to find a way to dynamically generate in JS
                document.getElementById('displayEntireDependencyGraph').onclick = function(){createEntireCourseDependencyGraph()};
                document.getElementById('clearGraph').onclick = function(){clearEntireGraph()};
                document.getElementById('MAT151').onclick = function(){changeNodeState('MAT151')};
                document.getElementById('MAT152').onclick = function(){changeNodeState('MAT152')};
                document.getElementById('MAT250').onclick = function(){changeNodeState('MAT250')};
                document.getElementById('CS111').onclick = function(){changeNodeState('CS111')};
                document.getElementById('CS112').onclick = function(){changeNodeState('CS112')};
                document.getElementById('CS205').onclick = function(){changeNodeState('CS205')};
                document.getElementById('CS206').onclick = function(){changeNodeState('CS206')};
                document.getElementById('CS211').onclick = function(){changeNodeState('CS211')};
                document.getElementById('CS214').onclick = function(){changeNodeState('CS214')};
                document.getElementById('CS314').onclick = function(){changeNodeState('CS314')};
                document.getElementById('CS323').onclick = function(){changeNodeState('CS323')};
                document.getElementById('CS336').onclick = function(){changeNodeState('CS336')};
                document.getElementById('CS344').onclick = function(){changeNodeState('CS344')};
                document.getElementById('CS352').onclick = function(){changeNodeState('CS352')};
                document.getElementById('CS415').onclick = function(){changeNodeState('CS415')};
                document.getElementById('CS416').onclick = function(){changeNodeState('CS416')};
                document.getElementById('CS417').onclick = function(){changeNodeState('CS417')};
                document.getElementById('CS419').onclick = function(){changeNodeState('CS419')};
                document.getElementById('CS428').onclick = function(){changeNodeState('CS428')};
                document.getElementById('CS431').onclick = function(){changeNodeState('CS431')};
                document.getElementById('CS440').onclick = function(){changeNodeState('CS440')};
         
                /*
                 * Changes the state of a node.
                 *   UNAVALIABLE_STATE = course cannot be taken
                 *   COMPLETED_STATE = completed course
                 *   READY_STATE = course avaliable to take  
                 *      
                 * Possible Actions:
                 * UNAVALIABLE_STATE Node -> READY_STATE Node
                 * READY_STATE -> COMPLETED_STATE Node
                 * 
                 * For a node to change to completed state, 
                 * the following conditions must be fulfilled:
                 * -course must "seen" in graph 
                 * -course must be in ready state
                 *    
                 */
                function changeNodeState(nodeId)
                {
                    printAllNodeState();
                    addNode(nodeId, COMPLETED_STATE_COLOR); //add Node to Particle System
                    nodeStateArray[nodeId] = COMPLETED_STATE; //mark course as taken
                    createNodeOutgoingEdges(nodeId); //add a node's outgoing edges
                }
                
                /*
                 * Determines if a course is ready to be taken.
                 * @param - 
                 *      nodeId - String Identitfer of node being inspected
                 * @return - true if node is in ready state; false otherwise 
                 */
                function isCourseReadyToBeTaken(nodeId)
                {
                    var courseObj = courseArray[nodeId];
                    var coursePrerequisites = courseObj.Prerequisite;
                    
                    //check if prequisites have been fulfilled
                    if(!(typeof coursePrerequisites === 'undefined'))
                    {
                        for(var i = 0; i < coursePrerequisites.length; i++)
                        {
                             var inspectCourseIdOrCourseGroup = coursePrerequisites[i];
                             
                             //Check if handling a group of courses
                             if(inspectCourseIdOrCourseGroup instanceof Array)
                             {
                                   //This handles the case if given a set of courses
                                   //only one course from this group needs to be fufilled.
                                   var isGroupfulfilled = false; 
                                   
                                   for(var j = 0; j < inspectCourseIdOrCourseGroup.length; j++)
                                   {
                                       var courseGroupElementId = inspectCourseIdOrCourseGroup[j];
                                       var prereqState = nodeStateArray[courseGroupElementId];
                                       
                                       //check if the prerequiste course from group is completed
                                       if(!(prereqState === 'undefined') && prereqState == 1)
                                       {
                                           isGroupfulfilled = true;
                                           break;
                                       }
                                   }
                                   
                                   //Course cannot be taken because group course prerequiste is not completed
                                   if(!isGroupfulfilled)
                                       return false;
                             }
                             
                             //single course to inspect
                             else
                             {
                                 var prereqState = nodeStateArray[inspectCourseIdOrCourseGroup]; //get state of the prerequiste course
                                 
                                 //check if the prerequiste course is completed
                                 if(!(prereqState === 'undefined') && prereqState == 1)
                                 {
                                       isGroupfulfilled = true;
                                       continue;
                                 }  
                                 
                                 //course prerequiste has not been fulfilled 
                                 return false;
                             }
                             
                        } //end outer for loop    
                    }
                    
                    return true; //case if the course has no prerequisites or course prerequistes has be fufilled 
                }
                
                /*
                 * Determines the state of the new node after this node has been introduced
                 * into the graph which is being built.
                 *  
                 * @param -
                 *      nodeId - String Identifier of a node that is being inspected
                 * 
                 * @return - the state of the node to be changed to
                 */
                function determineNodeState(nodeId)
                {
                    var currentNodeState = nodeStateArray[nodeId];
                    console.log("determineNodeState: " + nodeId + " = "  + currentNodeState);

                    //Case 1: Course is not in current graph or course cannot be taken yet 
                    if((typeof currentNodeState === 'undefined') || currentNodeState == 0)
                    {
                        //current node must be READY_STATEif prerequisites has been fulfilled
                        if(isCourseReadyToBeTaken(nodeId))
                            return READY_STATE;
                        
                        //current node must in UNAVALIABLE_STATE 
                        else
                            return UNAVALIABLE_STATE; 
                    } 
                   
                   return null;
                }
                
                /*
                 * Create a given node's outgoing edges.
                 * These new edges may allow unseen nodes to become 
                 * avaiable on the graph; New nodes may be created.
                 * @param 
                 *      nodeId - Node String Identifier (Course code) 
                 */
                function createNodeOutgoingEdges(nodeId)
                {
                    var curNodeOutgoingEdges = outgoingEdgeGraphArray[nodeId]; //an array containing node's outgoing edges
                    //console.log(curNodeOutgoingEdges);
                    
                    if(!(typeof curNodeOutgoingEdges === 'undefined'))
                    {
                        for(var i = 0; i < curNodeOutgoingEdges.length; i++)
                        {
                            var seenNodeId =  curNodeOutgoingEdges[i];
                            var edgeData = {length:7, directed:true, 'color':'black'};
                            //console.log(seenNodeId);
                            
                            //check if seen node exist in the Particle System
                            if(!doesNodeExist(seenNodeId))
                            {
                                var stateNum = determineNodeState(seenNodeId); //determine the state of the newly added Node
                                var color = numToColorMapping[stateNum]; //get the corresponding color of state number
                                //console.log(color);
                                
                                nodeStateArray[seenNodeId] = stateNum; //mark state of seen node  
                                addNode(seenNodeId, color); //add seen node to graph
                            }
                            
                             //TODO: case if a node is already seen, but new edges are being added which may make change the state of exiting nodes  
                            else
                            {
                                 var stateNum = determineNodeState(seenNodeId);
                                 if(stateNum != null)
                                 {
                                     var color = numToColorMapping[stateNum];
                                     particleSystem.getNode(seenNodeId).data.color = color;
                                 }    
                            }
                            
                           
                            particleSystem.addEdge(nodeId, seenNodeId, edgeData); //attach directed edge from given node to new node that is seen
                        }  
                    }
                }
                
                /*
                 * Add the node to the Particle System.
                 * @param
                 *      nodeId - String Identifier of the new node
                 *      color - state of the node
                 */
                function addNode(nodeId, nodeColor)
                {
                     var nodeData = {mass:1, label:nodeId, 'color': nodeColor, 'shape':'dot'}; //node data(key-value pair)
                     particleSystem.addNode(nodeId, nodeData); //add a node to the Particle System
                }
                
                /*
                 * Initial state of the Course Dependency Graph.
                 * This adds all the courses with no dependency.
                 */
                function initializeGraph()
                {
                    for(var courseCode in courseArray)
                    {
                        var courseObj = courseArray[courseCode];
                        var doesCourseHavePrereq = (typeof courseObj.Prerequisite === 'undefined')? false : true;
                        
                        if(!doesCourseHavePrereq)
                        {
                            addNode(courseCode, READY_STATE_COLOR); //add course that has no dependency to graph
                            nodeStateArray[courseCode] = READY_STATE; //mark course as "Ready" state   
                        }
                            
                    }
                }
                
                /*
                 * Determine all the outgoing edges for each node.     
                 * This will populate the outgoingEdgeGraphArray.
                 * JSON Format: {NodeId1: outgoingEdgeArray1[], NodeId2: outgoingEdgeArray2[]}
                 */
                function determineAllOutgoingEdges()
                {
                    //iterate all the courses
                    for(var courseCode in courseArray)
                    {
                        var courseObj = courseArray[courseCode];
                        var coursePrereq = courseObj.Prerequisite;
                                                  
                        //Check if field property exist
                        if(!(typeof coursePrereq === 'undefined'))
                        {
                            //iterate all of a course prerequisites
                            for(var i = 0; i < coursePrereq.length; i++)
                            {
                                var prereq = coursePrereq[i];
                                
                                //Check if value is an instance of an array
                                if((prereq instanceof Array))
                                {                                    
                                    //iterate all prereq with conditional OR
                                    for(var j = 0; j < prereq.length; j++)
                                    {
                                        if(!((outgoingEdgeGraphArray[prereq[j]]) instanceof Array))
                                        {
                                             outgoingEdgeGraphArray[prereq[j]] = new Array(); //create new array property
                                        }
                                        
                                        outgoingEdgeGraphArray[prereq[j]].push(courseCode); //add element to array
                                    }
                                }
                                
                                else   
                                {
                                    if(!((outgoingEdgeGraphArray[prereq]) instanceof Array))
                                    {
                                        outgoingEdgeGraphArray[prereq] = new Array(); //create new array property
                                    }
                                    
                                    outgoingEdgeGraphArray[prereq].push(courseCode); 
                                }
                            }
                        } 
                    }
                }
                
                /*
                 * Create the entire Course Dependency Graph.
                 */
                function createEntireCourseDependencyGraph()
                {
                    clearEntireGraph(particleSystem); //clear existing particle system
                    createAllNodesForDependencyGraph(particleSystem); //create all nodes; each node start off as a singleton
                    addDependencyEdges(particleSystem); // adds the dependency edges 
                }
                            
                /*
                 * Create all the nodes for the Course Dependency Graph
                 */
                function createAllNodesForDependencyGraph()
                {
                     //Iterate array to create nodes in Particle System
                    for(var key in courseArray)
                    {
                        var nodeId = key; //String Identifier of Node; Course code is used as the key
                        var nodeData = {mass:1, label:nodeId, 'color':'grey', 'shape':'dot'}; //node data(key-value pair)
                        particleSystem.addNode(nodeId, nodeData); //add a node to the Particle System
                    }
                }
                
                /*
                 * Add dependency edges for the entire Graph.
                 * This goes through each node and adds the node's outgoing edges.
                 */
                function addDependencyEdges()
                {
                    //Iterate array to create dependency edges
                    for(var key in courseArray)
                    {
                        var currentNodeId = key; //String Identifier of target node
                        var currentCourseObject = courseArray[currentNodeId]; //Get Course Object
                        var currentCoursePreq = currentCourseObject.Prerequisite; //Get the prerequisites of current course
                       
                        if(!(typeof currentCoursePreq === 'undefined'))
                        {
                           //Iterate dependency courses
                           for(var i = 0; i < currentCoursePreq.length; i++)
                           {
                               var dependencyNodeId = currentCoursePreq[i]; //String Identifier of source node

                               if(!(dependencyNodeId instanceof Array))
                               {
                                  //Add black directed edge (required course) from dependency node to current node
                                  particleSystem.addEdge(dependencyNodeId, currentNodeId, {length:7, directed:true, 'color':'black'}); //addEdge(sourceNode,targetNode,edgeData)
                               }

                               else
                               {
                                   //Iterate dependency courses logical OR
                                   for(var j = 0; j < dependencyNodeId.length; j++)
                                   {
                                       var dependencyNodeIdOR = dependencyNodeId[j];//String Identifier of source node

                                       //Add Gray directed edge from dependency node to current node
                                       particleSystem.addEdge(dependencyNodeIdOR, currentNodeId, {length:7, directed:true}); //addEdge(sourceNode,targetNode,edgeData)
                                   }
                               }
                           }
                        }
                    }   
                }
                
                /*
                 * Clears the entire Graph (Particle System) 
                 * Removes all the nodes and edges.
                 */
                function clearEntireGraph()
                {
                    nodeStateArray = {};//initialize a new object
                    
                     //Iterate array
                    for(var key in courseArray)
                    {
                        var currentNodeId = key; //String Identifier of current node
                        
                        //determine if node exist in Particle system
                        if(doesNodeExist(currentNodeId))
                            particleSystem.pruneNode(currentNodeId); //Removes the corresponding Node from the particle system (as well as any Edges in which it is a participant).
                    }  
                    
                }
                
                 /*
                 * Remove dependency edges for the entire Graph.
                 */
                function removeDependencyEdges()
                {
                    //Iterate array
                    for(var key in courseArray)
                    {
                        var currentNodeId = key; //String Identifier of current node
                        var currentNodeSourceEdgeArray = particleSystem.getEdgesFrom(currentNodeId); //Get edges in which the node is the source

                        //Iterate the source edges from current node and remove 
                        for(var i = 0; i < currentNodeSourceEdgeArray.length; i++)
                        {
                            var edgeObj = currentNodeSourceEdgeArray[i];
                            particleSystem.pruneEdge(edgeObj); //Removes edge from particle system
                        }
                    }   
                }
                
                /*
                 * Determines whether a node exist in the Particle System.
                 * @param 
                 *      nodeId - String Identifier of node
                 *      
                 * @return - true if node existsl false otherwise
                 */
                function doesNodeExist(nodeId)
                {
                    return (typeof particleSystem.getNode(nodeId) === 'undefined') ? false : true;
                }
                
                 /*
                 * Prints all outgoing edges.
                 */
                function printAllOutgoingEdges()
                {
                    console.log(outgoingEdgeGraphArray); //ctl + shift + j (Chrome Browser)
                    for(var courseCode in courseArray)
                    {
                        var outgoingEdges = outgoingEdgeGraphArray[courseCode];
                        
                        if(!(typeof outgoingEdges === 'undefined'))
                        {
                            for(var i = 0; i < outgoingEdges.length; i++)
                            {
                                console.log(courseCode + " -> " + outgoingEdges[i]);     
                            }  
                        } 
                    }
                }
                
                /*
                 * Prints the current state of each node
                 */
                function printAllNodeState()
                {
                    for(key in nodeStateArray)
                    {
                         console.log(key + " => " + nodeStateArray[key]);   
                    }
                }
        });
        </script>
    </body>
</html>
