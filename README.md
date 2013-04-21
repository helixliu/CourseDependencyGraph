CourseDependencyGraph
=====================

Course dependency graph of an academic major. This project uses ArborJS to build and display a dependency graph. Useful information can be obtained from dependency graph like course prerequisites, minimum number of semesters to complete a major (longest path), courses that are ready to be taken given completed courses, to name a few.

Bugs
=====================
-Single node in Particle System crashes code (ArborJS)  
-Does not work for IE9 (ArborJS)  
-Event trigger buttons fail to work if Particle System freezes  

My Suggestion for Improvments
=====================
-Add functionality to handle corequisite  
-Redesign algorithm and json data structure to handle complex course prerequisite  
+ Example Scenario: (C1 && (C2 || (C3 && C4)) || (C5 && C6)  
+ Example JSON Representation: OR:[[C1,OR:[C2,[C3,C4]]],[C5,C6]]  

-Handle overlapping (equivalent) courses  

The MIT License (MIT)
=====================

Copyright (C) 2013 rayedchan  
ArborJS by samizdatco [https://github.com/samizdatco/arbor]  

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
