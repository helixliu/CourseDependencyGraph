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
                    <input type="button" value="CS111: Introduction to Computer Science" id="CS111" /><br />
                    <input type="button" value="CS112: Data Structures" id="CS112" /><br />
                </form>
            </div>
        </div>
      
        <script language="javascript" type="text/javascript">
            $(document).ready(function()
            {
                /* Parameters for the Particle System. 
                 * The Particle System is used to represent my graph.
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
                var frictionValue = 0.5;
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
                 var courseArray = 
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
                 };
                 
                var outgoingEdgeGraphArray = {}; //create object; this will contain the outgoing edges of each node
                 
                //Trigger Event buttons
                document.getElementById('displayEntireDependencyGraph').onclick = function(){createEntireCourseDependencyGraph(particleSystem)};
                document.getElementById('clearGraph').onclick = function(){clearEntireGraph(particleSystem)};
                //document.getElementById('CS111').onclick = function(){changeNodeState("CS111")};
                
                
                document.getElementById('CS111').onclick = function(){determineAllOutgoingEdges()};
                document.getElementById('CS112').onclick = function(){printAllOutgoingEdges()};
                
                
                /*
                 * Determine all the outgoing edges for each node.     
                 * This will populate the outgoingEdgeGraphArray.
                 * NodeId : outgoingEdgeArray[]
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
                 * Prints all outgoing edges.
                 */
                function printAllOutgoingEdges()
                {
                    for(var courseCode in courseArray)
                    {
                        var outgoingEdges = outgoingEdgeGraphArray[courseCode];
                        
                        if(!(typeof outgoingEdges === 'undefined'))
                        {
                            for(var i = 0; i < outgoingEdges.length; i++)
                            {
                                alert(courseCode + " -> " + outgoingEdges[i]);     
                            }  
                        } 
                    }
                }
                
                
                /*
                 * Changes the state of a node.
                 *      Green Node = completed course
                 *      Gray Node = have not taken the course
                 *      Yellow Node = course availiable to take
                 *      Red Node = not able to take course 
                 */
                function changeNodeState(nodeId)
                {
                    var node = particleSystem.getNode(nodeId); //Get the Node Object 
                    var nodeData = node.data; //Get the Node data field
                    nodeData.color = 'green';
                }
                
                /*
                 * Create the entire Course Dependency Graph.
                 * @param
                 *      currentParticleSystem - particle system to modify
                 */
                function createEntireCourseDependencyGraph(currentParticleSystem)
                {
                    createAllNodesForDependencyGraph(currentParticleSystem); //create all nodes; each node start off as a singleton
                    addDependencyEdges(currentParticleSystem); // adds the dependency edges 
                }
                            
                /*
                 * Create all the nodes for the Course Dependency Graph
                 * @param
                 *      currentParticleSystem - particle system to modify
                 */
                function createAllNodesForDependencyGraph(currentParticleSystem)
                {
                     //Iterate array to create nodes in Particle System
                    for(var key in courseArray)
                    {
                        var nodeId = key; //String Identifier of Node; Course code is used as the key
                        var nodeData = {mass:1, label:nodeId, 'color':'grey', 'shape':'dot'}; //node data(key-value pair)
                        currentParticleSystem.addNode(nodeId, nodeData); //add a node to the Particle System
                    }
                }
                
                /*
                 * Add dependency edges for the entire Graph.
                 * @param
                 *      currentParticleSystem - particle system to modify
                 */
                function addDependencyEdges(currentParticleSystem)
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
                                  currentParticleSystem.addEdge(dependencyNodeId, currentNodeId, {length:7, directed:true, 'color':'black'}); //addEdge(sourceNode,targetNode,edgeData)
                               }

                               else
                               {
                                   //Iterate dependency courses logical OR
                                   for(var j = 0; j < dependencyNodeId.length; j++)
                                   {
                                       var dependencyNodeIdOR = dependencyNodeId[j];//String Identifier of source node

                                       //Add Gray directed edge from dependency node to current node
                                       currentParticleSystem.addEdge(dependencyNodeIdOR, currentNodeId, {length:7, directed:true}); //addEdge(sourceNode,targetNode,edgeData)
                                   }
                               }
                           }
                        }
                    }   
                }
                
                /*
                 * Clears the entire Graph (Particle System) 
                 * Removes all the nodes and edges.
                 * @param
                 *      currentParticleSystem - particle system to clear
                 */
                function clearEntireGraph(currentParticleSystem)
                {
                     //Iterate array
                    for(var key in courseArray)
                    {
                        var currentNodeId = key; //String Identifier of current node
                        currentParticleSystem.pruneNode(currentNodeId); //Removes the corresponding Node from the particle system (as well as any Edges in which it is a participant).
                    }  
                    
                }
                
                 /*
                 * Remove dependency edges for the entire Graph.
                 * currentParticleSystem - particle system to modify
                 */
                function removeDependencyEdges(currentParticleSystem)
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
                            currentParticleSystem.pruneEdge(edgeObj); //Removes edge from particle system
                        }
                    }   
                }       
        });
        </script>
    </body>
</html>
