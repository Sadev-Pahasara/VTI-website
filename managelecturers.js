document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded - starting initialization');
    
    const openModal = document.getElementById("openModal");
    const closeModal = document.getElementById("closeModal");
    const modal = document.getElementById("lecturerModal");
    const form = document.getElementById("lecturerForm");
    const tableBody = document.querySelector("#lecturerTable tbody");
    const modalTitle = document.getElementById("modalTitle");
    const saveBtn = document.getElementById("saveBtn");

    // Form elements for dynamic dropdowns
    const facultySelect = document.getElementById("lecturerFaculty");
    const courseSelect = document.getElementById("lecturerCourse");
    const moduleSelect = document.getElementById("lecturerModule");

    // Password toggle elements
    const passwordInput = document.getElementById('lecturerpassword');
    const toggleEye = document.getElementById('togglePassword');

    // Debug: Check if elements are found
    console.log('openModal:', openModal);
    console.log('closeModal:', closeModal);
    console.log('modal:', modal);
    console.log('form:', form);
    console.log('tableBody:', tableBody);
    console.log('facultySelect:', facultySelect);
    console.log('courseSelect:', courseSelect);
    console.log('moduleSelect:', moduleSelect);

    if (!openModal || !closeModal || !modal || !form || !tableBody) {
        console.error('One or more required elements not found');
        alert('Error: Some page elements are missing. Please refresh the page.');
        return;
    }

    let editingRow = null;
    let editingId = null;

    // UPDATED DATA STRUCTURE WITH ACTUAL DATABASE IDs
    const facultyData = {
        "BUSINESS FACULTY": {
            "BUS001": {
                "name": "Business Administration",
                "modules": {
                    "MOD001": "Principles of Management",
                    "MOD002": "Financial Accounting", 
                    "MOD003": "Business Mathematics",
                    "MOD004": "Business Communication",
                    "MOD005": "Microeconomics",
                    "MOD006": "Macroeconomics",
                    "MOD007": "Marketing Principles",
                    "MOD008": "Business Statistics",
                    "MOD009": "Organizational Behavior",
                    "MOD010": "Corporate Finance",
                    "MOD011": "Human Resource Management",
                    "MOD012": "Operations Management",
                    "MOD013": "Business Law",
                    "MOD014": "Strategic Management",
                    "MOD015": "International Business",
                    "MOD246": "Business Ethics",
                    "MOD247": "Supply Chain Management"
                }
            },
            "BUS002": {
                "name": "Marketing and Finance",
                "modules": {
                    "MOD016": "Consumer Behavior",
                    "MOD017": "Financial Markets",
                    "MOD018": "Marketing Research",
                    "MOD019": "Investment Analysis",
                    "MOD020": "Digital Marketing",
                    "MOD021": "Corporate Banking",
                    "MOD022": "Brand Management",
                    "MOD023": "Derivatives and Risk Management",
                    "MOD024": "Sales Management",
                    "MOD025": "International Finance",
                    "MOD283": "Consumer Analytics"
                }
            },
            "BUS003": {
                "name": "HR Management",
                "modules": {
                    "MOD026": "Introduction to HRM",
                    "MOD027": "Recruitment and Selection",
                    "MOD028": "Training and Development",
                    "MOD029": "Compensation Management",
                    "MOD030": "Labor Relations",
                    "MOD031": "Performance Management",
                    "MOD032": "Strategic HRM",
                    "MOD284": "Talent Management"
                }
            },
            "BUS004": {
                "name": "Entrepreneurship", 
                "modules": {
                    "MOD033": "Entrepreneurial Mindset",
                    "MOD034": "Business Plan Development",
                    "MOD035": "Venture Capital",
                    "MOD036": "Small Business Management",
                    "MOD037": "Innovation Management",
                    "MOD038": "Social Entrepreneurship",
                    "MOD285": "Family Business Management"
                }
            }
        },
        "COMPUTING FACULTY": {
            "COMP001": {
                "name": "Computer Science",
                "modules": {
                    "MOD039": "Introduction to Programming",
                    "MOD040": "Discrete Mathematics",
                    "MOD041": "Computer Fundamentals",
                    "MOD042": "Data Structures",
                    "MOD043": "Algorithms",
                    "MOD044": "Object-Oriented Programming",
                    "MOD045": "Database Systems",
                    "MOD046": "Computer Networks",
                    "MOD047": "Software Engineering",
                    "MOD048": "Web Development",
                    "MOD049": "Artificial Intelligence",
                    "MOD050": "Operating Systems",
                    "MOD244": "Mobile App Development",
                    "MOD245": "Cloud Computing",
                    "MOD286": "Big Data Analytics"
                }
            },
            "COMP002": {
                "name": "Software Engineering",
                "modules": {
                    "MOD051": "Software Requirements",
                    "MOD052": "Software Design",
                    "MOD053": "Software Construction",
                    "MOD054": "Software Testing",
                    "MOD055": "Software Project Management",
                    "MOD056": "Software Maintenance",
                    "MOD057": "Agile Development",
                    "MOD253": "Mobile Software Engineering",
                    "MOD287": "DevOps Practices"
                }
            },
            "COMP003": {
                "name": "Information Systems",
                "modules": {
                    "MOD058": "Business Information Systems",
                    "MOD059": "Systems Analysis and Design",
                    "MOD060": "Enterprise Systems",
                    "MOD061": "IT Project Management",
                    "MOD062": "Business Intelligence",
                    "MOD063": "E-Commerce Systems",
                    "MOD254": "Cybersecurity Management",
                    "MOD288": "IT Governance"
                }
            },
            "COMP004": {
                "name": "Cybersecurity",
                "modules": {
                    "MOD064": "Network Security",
                    "MOD065": "Cryptography",
                    "MOD066": "Ethical Hacking",
                    "MOD067": "Digital Forensics",
                    "MOD068": "Security Management",
                    "MOD069": "Cloud Security",
                    "MOD255": "Network Defense",
                    "MOD289": "Digital Forensics"
                }
            },
            "COMP005": {
                "name": "Networking",
                "modules": {
                    "MOD070": "Network Fundamentals",
                    "MOD071": "Routing and Switching",
                    "MOD072": "Wireless Networks",
                    "MOD073": "Network Administration",
                    "MOD074": "Voice over IP",
                    "MOD075": "Network Design",
                    "MOD256": "Network Security",
                    "MOD290": "Network Programming"
                }
            }
        },
        "ENGINEERING FACULTY": {
            "ENG001": {
                "name": "Civil Engineering",
                "modules": {
                    "MOD076": "Engineering Mechanics",
                    "MOD077": "Engineering Drawing",
                    "MOD078": "Surveying",
                    "MOD079": "Construction Materials",
                    "MOD080": "Structural Analysis",
                    "MOD081": "Geotechnical Engineering",
                    "MOD082": "Hydraulics",
                    "MOD083": "Transportation Engineering",
                    "MOD084": "Environmental Engineering",
                    "MOD257": "Structural Design",
                    "MOD291": "Construction Management"
                }
            },
            "ENG002": {
                "name": "Mechanical Engineering",
                "modules": {
                    "MOD085": "Engineering Thermodynamics",
                    "MOD086": "Engineering Graphics",
                    "MOD087": "Manufacturing Processes",
                    "MOD088": "Mechanics of Materials",
                    "MOD089": "Fluid Mechanics",
                    "MOD090": "Heat Transfer",
                    "MOD091": "Machine Design",
                    "MOD092": "Control Systems",
                    "MOD248": "Robotics and Automation",
                    "MOD292": "Renewable Energy Systems"
                }
            },
            "ENG003": {
                "name": "Electrical Engineering",
                "modules": {
                    "MOD093": "Circuit Analysis",
                    "MOD094": "Electromagnetic Fields",
                    "MOD095": "Digital Electronics",
                    "MOD096": "Signals and Systems",
                    "MOD097": "Power Systems",
                    "MOD098": "Electrical Machines",
                    "MOD099": "Power Electronics",
                    "MOD100": "Renewable Energy Systems",
                    "MOD249": "Smart Grid Technologies",
                    "MOD293": "Power System Protection"
                }
            },
            "ENG004": {
                "name": "Electronic Engineering",
                "modules": {
                    "MOD101": "Analog Electronics",
                    "MOD102": "Communication Systems",
                    "MOD103": "Microprocessors",
                    "MOD104": "Control Systems",
                    "MOD105": "VLSI Design",
                    "MOD106": "Embedded Systems",
                    "MOD258": "Digital Signal Processing",
                    "MOD294": "Wireless Communications"
                }
            },
            "ENG005": {
                "name": "Robotics",
                "modules": {
                    "MOD107": "Introduction to Robotics",
                    "MOD108": "Robot Kinematics",
                    "MOD109": "Robot Dynamics",
                    "MOD110": "Robot Vision",
                    "MOD111": "Autonomous Systems",
                    "MOD259": "Robot Programming",
                    "MOD295": "Computer Vision"
                }
            },
            "ENG006": {
                "name": "Mechatronics",
                "modules": {
                    "MOD112": "Mechatronic Systems",
                    "MOD113": "Sensors and Actuators",
                    "MOD114": "PLC Programming",
                    "MOD115": "Industrial Automation",
                    "MOD116": "Mechatronic Design",
                    "MOD260": "Automation Systems",
                    "MOD296": "Mechatronic Project"
                }
            }
        },
        "LAW FACULTY": {
            "LAW001": {
                "name": "Law",
                "modules": {
                    "MOD117": "Constitutional Law",
                    "MOD118": "Contract Law",
                    "MOD119": "Criminal Law",
                    "MOD120": "Tort Law",
                    "MOD121": "Property Law",
                    "MOD122": "Administrative Law",
                    "MOD123": "Evidence Law",
                    "MOD124": "Legal Research",
                    "MOD261": "Family Law"
                }
            },
            "LAW002": {
                "name": "International Law",
                "modules": {
                    "MOD125": "Public International Law",
                    "MOD126": "International Human Rights",
                    "MOD127": "International Trade Law",
                    "MOD128": "Law of the Sea",
                    "MOD129": "International Criminal Law",
                    "MOD262": "International Environmental Law"
                }
            },
            "LAW003": {
                "name": "Business Law",
                "modules": {
                    "MOD130": "Corporate Law",
                    "MOD131": "Commercial Law",
                    "MOD132": "Taxation Law",
                    "MOD133": "Insolvency Law",
                    "MOD134": "Competition Law",
                    "MOD263": "Intellectual Property Law"
                }
            }
        },
        "HEALTH SCIENCES FACULTY": {
            "HEALTH001": {
                "name": "Nursing",
                "modules": {
                    "MOD135": "Anatomy and Physiology",
                    "MOD136": "Nursing Fundamentals",
                    "MOD137": "Pharmacology",
                    "MOD138": "Medical-Surgical Nursing",
                    "MOD139": "Pediatric Nursing",
                    "MOD140": "Mental Health Nursing",
                    "MOD141": "Community Health Nursing",
                    "MOD250": "Critical Care Nursing",
                    "MOD297": "Nursing Leadership"
                }
            },
            "HEALTH002": {
                "name": "Pharmacy",
                "modules": {
                    "MOD142": "Pharmaceutical Chemistry",
                    "MOD143": "Pharmaceutics",
                    "MOD144": "Pharmacology",
                    "MOD145": "Medicinal Chemistry",
                    "MOD146": "Clinical Pharmacy",
                    "MOD147": "Pharmacy Practice",
                    "MOD264": "Pharmaceutical Analysis"
                }
            },
            "HEALTH003": {
                "name": "Public Health",
                "modules": {
                    "MOD148": "Epidemiology",
                    "MOD149": "Biostatistics",
                    "MOD150": "Health Policy",
                    "MOD151": "Environmental Health",
                    "MOD152": "Health Promotion",
                    "MOD265": "Global Health"
                }
            },
            "HEALTH004": {
                "name": "Physiotherapy",
                "modules": {
                    "MOD153": "Human Anatomy",
                    "MOD154": "Exercise Physiology",
                    "MOD155": "Musculoskeletal Physiotherapy",
                    "MOD156": "Neurological Physiotherapy",
                    "MOD157": "Cardiorespiratory Physiotherapy",
                    "MOD266": "Sports Physiotherapy"
                }
            }
        },
        "ARTS & HUMANITIES FACULTY": {
            "ARTS001": {
                "name": "Psychology",
                "modules": {
                    "MOD158": "Introduction to Psychology",
                    "MOD159": "Developmental Psychology",
                    "MOD160": "Social Psychology",
                    "MOD161": "Cognitive Psychology",
                    "MOD162": "Abnormal Psychology",
                    "MOD163": "Research Methods",
                    "MOD164": "Biological Psychology",
                    "MOD251": "Industrial Psychology",
                    "MOD298": "Psychological Assessment"
                }
            },
            "ARTS002": {
                "name": "English",
                "modules": {
                    "MOD165": "Academic Writing",
                    "MOD166": "British Literature",
                    "MOD167": "American Literature",
                    "MOD168": "Literary Theory",
                    "MOD169": "Creative Writing",
                    "MOD267": "World Literature"
                }
            },
            "ARTS003": {
                "name": "Communication",
                "modules": {
                    "MOD170": "Mass Communication",
                    "MOD171": "Interpersonal Communication",
                    "MOD172": "Public Speaking",
                    "MOD173": "Media Writing",
                    "MOD174": "Digital Media Production",
                    "MOD268": "Intercultural Communication"
                }
            },
            "ARTS004": {
                "name": "Sociology",
                "modules": {
                    "MOD175": "Introduction to Sociology",
                    "MOD176": "Social Theory",
                    "MOD177": "Social Research Methods",
                    "MOD178": "Social Inequality",
                    "MOD179": "Urban Sociology",
                    "MOD269": "Criminology"
                }
            }
        },
        "CREATIVE ARTS FACULTY": {
            "CREATIVE001": {
                "name": "Interior Design",
                "modules": {
                    "MOD180": "Design Fundamentals",
                    "MOD181": "Space Planning",
                    "MOD182": "Materials and Finishes",
                    "MOD183": "Lighting Design",
                    "MOD184": "Professional Practice",
                    "MOD270": "Sustainable Design"
                }
            },
            "CREATIVE002": {
                "name": "Architecture",
                "modules": {
                    "MOD185": "Architectural Design Studio I",
                    "MOD186": "Architectural History",
                    "MOD187": "Building Technology",
                    "MOD188": "Environmental Systems",
                    "MOD189": "Urban Design",
                    "MOD252": "Sustainable Design",
                    "MOD299": "Digital Design Tools"
                }
            },
            "CREATIVE003": {
                "name": "Graphic Design",
                "modules": {
                    "MOD190": "Typography",
                    "MOD191": "Color Theory",
                    "MOD192": "Digital Imaging",
                    "MOD193": "Layout Design",
                    "MOD194": "Brand Identity",
                    "MOD271": "Motion Graphics"
                }
            },
            "CREATIVE004": {
                "name": "Fashion",
                "modules": {
                    "MOD195": "Fashion Illustration",
                    "MOD196": "Textile Science",
                    "MOD197": "Pattern Making",
                    "MOD198": "Fashion Marketing",
                    "MOD199": "Fashion History",
                    "MOD272": "Fashion Entrepreneurship"
                }
            }
        },
        "SHORT COURSES & PROFESSIONAL DEVELOPMENT": {
            "SHORT001": {
                "name": "Web Development Bootcamp",
                "modules": {
                    "MOD200": "HTML & CSS Fundamentals",
                    "MOD201": "JavaScript Programming",
                    "MOD202": "Frontend Frameworks",
                    "MOD203": "Backend Development",
                    "MOD204": "Database Integration",
                    "MOD273": "Web Security",
                    "MOD300": "E-commerce Development"
                }
            },
            "SHORT002": {
                "name": "Digital Marketing",
                "modules": {
                    "MOD205": "SEO Strategies",
                    "MOD206": "Social Media Marketing",
                    "MOD207": "Content Marketing",
                    "MOD208": "Email Marketing",
                    "MOD209": "Analytics and Measurement",
                    "MOD274": "Digital Strategy"
                }
            },
            "SHORT003": {
                "name": "UI/UX Design",
                "modules": {
                    "MOD210": "User Research",
                    "MOD211": "Wireframing and Prototyping",
                    "MOD212": "Visual Design Principles",
                    "MOD213": "Usability Testing",
                    "MOD275": "Interaction Design"
                }
            },
            "SHORT004": {
                "name": "Data Analytics",
                "modules": {
                    "MOD214": "Data Analysis with Excel",
                    "MOD215": "SQL for Analytics",
                    "MOD216": "Python for Data Science",
                    "MOD217": "Data Visualization",
                    "MOD218": "Statistical Analysis",
                    "MOD276": "Machine Learning Basics"
                }
            },
            "SHORT005": {
                "name": "Project Management",
                "modules": {
                    "MOD219": "Project Planning",
                    "MOD220": "Risk Management",
                    "MOD221": "Agile Methodologies",
                    "MOD222": "Stakeholder Management",
                    "MOD277": "Project Leadership"
                }
            },
            "SHORT006": {
                "name": "IELTS & Language Training",
                "modules": {
                    "MOD223": "Reading Skills",
                    "MOD224": "Writing Skills",
                    "MOD225": "Listening Skills",
                    "MOD226": "Speaking Skills",
                    "MOD278": "Test Strategies"
                }
            },
            "SHORT007": {
                "name": "Professional Certifications",
                "modules": {
                    "MOD227": "Cloud Fundamentals",
                    "MOD228": "Network Certification Prep",
                    "MOD229": "Microsoft Technologies",
                    "MOD230": "Adobe Creative Suite",
                    "MOD279": "Security Certifications"
                }
            },
            "SHORT008": {
                "name": "Plumbing",
                "modules": {
                    "MOD231": "Pipe Systems",
                    "MOD232": "Fixture Installation",
                    "MOD233": "Drainage Systems",
                    "MOD234": "Plumbing Codes",
                    "MOD280": "Commercial Plumbing"
                }
            },
            "SHORT009": {
                "name": "Welding",
                "modules": {
                    "MOD235": "Arc Welding",
                    "MOD236": "MIG Welding",
                    "MOD237": "TIG Welding",
                    "MOD238": "Welding Safety",
                    "MOD281": "Pipe Welding"
                }
            },
            "SHORT010": {
                "name": "Hotel Management",
                "modules": {
                    "MOD239": "Front Office Operations",
                    "MOD240": "Housekeeping Management",
                    "MOD241": "Food and Beverage Service",
                    "MOD242": "Hotel Marketing",
                    "MOD243": "Revenue Management",
                    "MOD282": "Event Management"
                }
            }
        }
    };

    // Load lecturers on page load
    loadLecturers();

    // Password toggle functionality
    if (toggleEye && passwordInput) {
        toggleEye.addEventListener('click', () => {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleEye.src = 'Eye.png';
            } else {
                passwordInput.type = 'password';
                toggleEye.src = 'eye-crossed.png';
            }
        });
    }

    // Open Modal for Add
    openModal.addEventListener("click", function() {
        console.log('Add Lecturer button clicked');
        
        modal.style.display = "flex";
        modalTitle.textContent = "Add Lecturer";
        saveBtn.textContent = "Add Lecturer";
        form.reset();
        editingRow = null;
        editingId = null;
        
        // Reset password field to hidden when opening modal
        if (passwordInput) {
            passwordInput.type = 'password';
            if (toggleEye) {
                toggleEye.src = 'eye-crossed.png';
            }
        }
        
        // Load faculties when opening modal
        loadFaculties();
        
        // Reset course and module dropdowns
        if (courseSelect) {
            courseSelect.innerHTML = '<option value="" selected disabled>Choose Course</option>';
        }
        if (moduleSelect) {
            moduleSelect.innerHTML = '<option value="" selected disabled>Choose Module</option>';
        }
        
        console.log('Modal opened successfully');
    });

    // Close Modal
    closeModal.addEventListener("click", function() {
        console.log('Close button clicked');
        modal.style.display = "none";
    });

    // Close modal when clicking outside
    modal.addEventListener("click", function(e) {
        if (e.target === modal) {
            modal.style.display = "none";
        }
    });

    // Faculty change event - load courses based on selected faculty
    if (facultySelect) {
        facultySelect.addEventListener("change", function() {
            console.log('Faculty changed:', this.value);
            const facultyName = this.value;
            if (facultyName) {
                loadCourses(facultyName);
            } else {
                if (courseSelect) {
                    courseSelect.innerHTML = '<option value="" selected disabled>Choose Course</option>';
                }
                if (moduleSelect) {
                    moduleSelect.innerHTML = '<option value="" selected disabled>Choose Module</option>';
                }
            }
        });
    } else {
        console.error('Faculty select element not found!');
    }

    // Course change event - load modules based on selected course
    if (courseSelect) {
        courseSelect.addEventListener("change", function() {
            console.log('Course changed:', this.value);
            const courseName = this.value;
            if (courseName) {
                loadModules(courseName);
            } else {
                if (moduleSelect) {
                    moduleSelect.innerHTML = '<option value="" selected disabled>Choose Module</option>';
                }
            }
        });
    } else {
        console.error('Course select element not found!');
    }

    // Add or Update Lecturer
    form.addEventListener("submit", (e) => {
        e.preventDefault();
        console.log('Form submitted');

        const formData = new FormData(form);
        
        // Include editing ID if in edit mode
        if (editingId) {
            formData.append('editing_id', editingId);
        }

        // Debug: Log form data
        for (let [key, value] of formData.entries()) {
            console.log(key + ': ' + value);
        }

        fetch('lecturers_backend.php?action=save_lecturer', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(text => {
            console.log('Server response:', text);
            try {
                const data = JSON.parse(text);
                if (data.success) {
                    loadLecturers();
                    form.reset();
                    modal.style.display = "none";
                    editingRow = null;
                    editingId = null;
                    alert('Lecturer saved successfully!');
                    
                    if (passwordInput) {
                        passwordInput.type = 'password';
                        if (toggleEye) {
                            toggleEye.src = 'eye-crossed.png';
                        }
                    }
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (e) {
                console.error('Failed to parse JSON:', text);
                alert('Server returned invalid response. Check console for details.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error saving lecturer: ' + error.message);
        });
    });

    // Handle Edit & Delete
    tableBody.addEventListener("click", (e) => {
        if (e.target.classList.contains("delete-btn")) {
            const row = e.target.closest("tr");
            const lecid = row.cells[0].textContent;
            
            if (confirm('Are you sure you want to delete this lecturer?')) {
                deleteLecturer(lecid, row);
            }
        }

        if (e.target.classList.contains("edit-btn")) {
            const row = e.target.closest("tr");
            editingRow = row;
            editingId = row.cells[0].textContent;
            
            modal.style.display = "flex";
            modalTitle.textContent = "Edit Lecturer";
            saveBtn.textContent = "Update Lecturer";

            // Pre-fill form
            document.getElementById("lecturerId").value = row.cells[0].textContent;
            document.getElementById("lecturerFName").value = row.cells[1].textContent;
            document.getElementById("lecturerLName").value = row.cells[2].textContent;
            document.getElementById("lecturerEmail").value = row.cells[3].textContent;
            document.getElementById("lecturerusername").value = row.cells[4].textContent;
            document.getElementById("lecturerpassword").value = row.cells[5].textContent;
            document.getElementById("lecturerNumber").value = row.cells[9].textContent;
            
            // Load faculties and set the selected one
            loadFaculties().then(() => {
                if (facultySelect) {
                    const facultyValue = row.cells[6].textContent;
                    facultySelect.value = facultyValue;
                    // Load courses based on selected faculty
                    return loadCourses(facultyValue);
                }
            }).then(() => {
                if (courseSelect) {
                    // Extract course ID from the row data
                    // We need to get the actual course ID from the database
                    // For now, we'll try to find it by matching the displayed name
                    const courseDisplayName = row.cells[7].textContent;
                    let courseId = findCourseIdByDisplayName(courseDisplayName);
                    if (courseId) {
                        courseSelect.value = courseId;
                        // Load modules based on selected course
                        return loadModules(courseId);
                    }
                }
            }).then(() => {
                if (moduleSelect) {
                    // Extract module ID from the row data
                    const moduleDisplayName = row.cells[8].textContent;
                    let moduleId = findModuleIdByDisplayName(moduleDisplayName);
                    if (moduleId) {
                        moduleSelect.value = moduleId;
                    }
                }
            });
            
            // Reset password to hidden when editing
            if (passwordInput) {
                passwordInput.type = 'password';
                if (toggleEye) {
                    toggleEye.src = 'eye-crossed.png';
                }
            }
        }
    });

    // Search functionality
    const searchInput = document.getElementById("searchInput");
    if (searchInput) {
        searchInput.addEventListener("keyup", () => {
            const filter = searchInput.value.trim();
            
            if (filter.length === 0) {
                loadLecturers();
                return;
            }
            
            fetch(`lecturers_backend.php?action=search_lecturers&query=${encodeURIComponent(filter)}`)
                .then(response => response.json())
                .then(data => {
                    displayLecturers(data);
                })
                .catch(error => {
                    console.error('Error searching:', error);
                });
        });
    }

    // Function to load lecturers from database
    function loadLecturers() {
        fetch('lecturers_backend.php?action=get_lecturers')
            .then(response => response.json())
            .then(data => {
                displayLecturers(data);
            })
            .catch(error => {
                console.error('Error loading lecturers:', error);
            });
    }

    // Function to display lecturers in table
    function displayLecturers(lecturers) {
        tableBody.innerHTML = '';
        
        if (Array.isArray(lecturers)) {
            lecturers.forEach(lecturer => {
                const row = document.createElement("tr");
                
                // Convert course ID to name for display
                let courseDisplay = lecturer.course;
                let moduleDisplay = lecturer.module;
                
                // Find course name
                for (const faculty of Object.keys(facultyData)) {
                    for (const courseId of Object.keys(facultyData[faculty])) {
                        if (courseId === lecturer.course) {
                            courseDisplay = facultyData[faculty][courseId].name;
                            // Find module name
                            if (facultyData[faculty][courseId].modules[lecturer.module]) {
                                moduleDisplay = facultyData[faculty][courseId].modules[lecturer.module];
                            }
                            break;
                        }
                    }
                }
                
                row.innerHTML = `
                    <td>${lecturer.lecid}</td>
                    <td>${lecturer.first_name}</td>
                    <td>${lecturer.last_name}</td>
                    <td>${lecturer.email}</td>
                    <td>${lecturer.username}</td>
                    <td>${lecturer.password}</td>
                    <td>${lecturer.faculty}</td>
                    <td>${courseDisplay}</td>
                    <td>${moduleDisplay}</td>
                    <td>${lecturer.mobile_number}</td>
                    <td>
                        <button class="edit-btn">Edit</button>
                        <button class="delete-btn">Delete</button>
                    </td>
                `;
                tableBody.appendChild(row);
            });
        }
    }

    // Function to delete lecturer
    function deleteLecturer(lecid, row) {
        const formData = new FormData();
        formData.append('lecid', lecid);
        
        fetch('lecturers_backend.php?action=delete_lecturer', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                row.remove();
                alert('Lecturer deleted successfully!');
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting lecturer');
        });
    }

    // Function to load faculties from JavaScript data
    function loadFaculties() {
        console.log('Loading faculties from JS data...');
        
        if (facultySelect) {
            facultySelect.innerHTML = '<option value="" selected disabled>Choose Faculty</option>';
            
            // Get all faculty names from the data object
            const facultyNames = Object.keys(facultyData);
            
            facultyNames.forEach(facultyName => {
                const option = document.createElement("option");
                option.value = facultyName;
                option.textContent = facultyName;
                facultySelect.appendChild(option);
            });
            
            console.log('Faculties loaded successfully:', facultyNames.length);
        } else {
            console.error('Faculty select element not found');
        }
        
        return Promise.resolve();
    }

    // Function to load courses based on selected faculty
    function loadCourses(facultyName) {
        console.log('Loading courses for faculty:', facultyName);
        
        if (courseSelect && facultyData[facultyName]) {
            courseSelect.innerHTML = '<option value="" selected disabled>Choose Course</option>';
            
            // Get courses for the selected faculty
            const courses = Object.keys(facultyData[facultyName]);
            
            courses.forEach(courseId => {
                const option = document.createElement("option");
                option.value = courseId; // Store course ID (e.g., "BUS001")
                option.textContent = facultyData[facultyName][courseId].name + " (" + courseId + ")"; // Display: Course Name (Course ID)
                courseSelect.appendChild(option);
            });
            
            console.log('Courses loaded:', courses.length);
        } else {
            if (courseSelect) {
                courseSelect.innerHTML = '<option value="" selected disabled>Choose Course</option>';
            }
            console.warn('No courses found for faculty:', facultyName);
        }
        
        return Promise.resolve();
    }

    // Function to load modules based on selected course
    function loadModules(courseId) {
        console.log('Loading modules for course:', courseId);
        
        if (moduleSelect) {
            moduleSelect.innerHTML = '<option value="" selected disabled>Choose Module</option>';
            
            // Find which faculty this course belongs to and get modules
            let modules = {};
            for (const faculty of Object.keys(facultyData)) {
                if (facultyData[faculty][courseId]) {
                    modules = facultyData[faculty][courseId].modules;
                    break;
                }
            }
            
            if (Object.keys(modules).length > 0) {
                Object.keys(modules).forEach(moduleId => {
                    const option = document.createElement("option");
                    option.value = moduleId; // Store module ID (e.g., "MOD001")
                    option.textContent = modules[moduleId] + " (" + moduleId + ")"; // Display: Module Name (Module ID)
                    moduleSelect.appendChild(option);
                });
                console.log('Modules loaded:', Object.keys(modules).length);
            } else {
                console.warn('No modules found for course:', courseId);
            }
        }
        
        return Promise.resolve();
    }

    // Helper function to find course ID by display name
    function findCourseIdByDisplayName(displayName) {
        for (const faculty of Object.keys(facultyData)) {
            for (const courseId of Object.keys(facultyData[faculty])) {
                if (facultyData[faculty][courseId].name === displayName) {
                    return courseId;
                }
            }
        }
        return null;
    }

    // Helper function to find module ID by display name
    function findModuleIdByDisplayName(displayName) {
        for (const faculty of Object.keys(facultyData)) {
            for (const courseId of Object.keys(facultyData[faculty])) {
                for (const moduleId of Object.keys(facultyData[faculty][courseId].modules)) {
                    if (facultyData[faculty][courseId].modules[moduleId] === displayName) {
                        return moduleId;
                    }
                }
            }
        }
        return null;
    }
});