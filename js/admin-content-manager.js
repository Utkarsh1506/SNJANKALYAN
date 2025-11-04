/* Admin Content Management System - CRUD Operations
 * Manages: Certifications, Gallery, Team Members, Donors, News
 */

const ContentManager = (function() {
    // Storage keys
    const KEYS = {
        CERTIFICATIONS: 'charity_certifications',
        GALLERY: 'charity_gallery',
        TEAM: 'charity_team',
        DONORS: 'charity_donors',
        NEWS: 'charity_news'
    };

    // Initialize with default data if empty
    function init() {
        // Initialize Certifications
        if (!localStorage.getItem(KEYS.CERTIFICATIONS)) {
            const defaultCerts = [
                {
                    id: 1,
                    title: "Registration Certificate",
                    description: "Official registration document from the government",
                    pdfUrl: "docs/certificates/registration.pdf",
                    issueDate: "2020-01-15",
                    icon: "📜"
                },
                {
                    id: 2,
                    title: "Tax Exemption Certificate",
                    description: "80G Tax exemption certificate",
                    pdfUrl: "docs/certificates/tax-exemption.pdf",
                    issueDate: "2020-03-20",
                    icon: "💰"
                },
                {
                    id: 3,
                    title: "NGO Certification",
                    description: "Non-profit organization certification",
                    pdfUrl: "docs/certificates/ngo-cert.pdf",
                    issueDate: "2020-06-10",
                    icon: "🏆"
                }
            ];
            localStorage.setItem(KEYS.CERTIFICATIONS, JSON.stringify(defaultCerts));
        }

        // Initialize Team
        if (!localStorage.getItem(KEYS.TEAM)) {
            const defaultTeam = [
                {
                    id: 1,
                    name: "Dr. Shivanand Nautiyal",
                    position: "Founder & Chairman",
                    description: "Former Education and Parvatiya Samiti Minister",
                    image: "img/volenteer/WhatsApp Image 2025-05-11 at 11.40.36 AM_05112025114347.webp",
                    facebook: "#",
                    phone: "",
                    twitter: "#"
                },
                {
                    id: 2,
                    name: "Surendra Nautiyal",
                    position: "Co-Founder & Director",
                    description: "Adhyaksha (Uttrakhand)",
                    image: "img/volenteer/surendra.webp",
                    facebook: "#",
                    phone: "+919837207546",
                    twitter: "#"
                },
                {
                    id: 3,
                    name: "Ashutosh Pokhariyal",
                    position: "Upadhyaksha",
                    description: "Vice President",
                    image: "img/volenteer/ashutosh.webp",
                    facebook: "#",
                    phone: "",
                    twitter: "#"
                },
                {
                    id: 4,
                    name: "Shailja Devi",
                    position: "Koshadhyaksha",
                    description: "Treasurer",
                    image: "img/volenteer/shailja.jpeg",
                    facebook: "#",
                    phone: "",
                    twitter: "#"
                },
                {
                    id: 5,
                    name: "Naveen",
                    position: "Program Coordinator",
                    description: "Event Management",
                    image: "img/volenteer/naveen.webp",
                    facebook: "#",
                    phone: "",
                    twitter: "#"
                },
                {
                    id: 6,
                    name: "Sanjay",
                    position: "Social Media Manager",
                    description: "Communications Lead",
                    image: "img/volenteer/sanjay.webp",
                    facebook: "#",
                    phone: "",
                    twitter: "#"
                },
                {
                    id: 7,
                    name: "Negi",
                    position: "Community Outreach",
                    description: "Field Coordinator",
                    image: "img/volenteer/negi.webp",
                    facebook: "#",
                    phone: "",
                    twitter: "#"
                }
            ];
            localStorage.setItem(KEYS.TEAM, JSON.stringify(defaultTeam));
        }

        // Initialize Gallery
        if (!localStorage.getItem(KEYS.GALLERY)) {
            const defaultGallery = [
                {
                    id: 1,
                    title: "Community Event 2024",
                    description: "Annual community gathering",
                    image: "img/gallery/1.png",
                    category: "events",
                    date: "2024-03-15"
                },
                {
                    id: 2,
                    title: "Education Program",
                    description: "Student support initiative",
                    image: "img/gallery/2.png",
                    category: "education",
                    date: "2024-04-20"
                },
                {
                    id: 3,
                    title: "Health Camp",
                    description: "Free medical checkup camp",
                    image: "img/gallery/3.png",
                    category: "health",
                    date: "2024-05-10"
                }
            ];
            localStorage.setItem(KEYS.GALLERY, JSON.stringify(defaultGallery));
        }

        // Initialize Donors
        if (!localStorage.getItem(KEYS.DONORS)) {
            const defaultDonors = [
                {
                    id: 1,
                    name: "Anonymous Donor",
                    amount: 50000,
                    date: "2024-01-15",
                    type: "General Fund",
                    message: "Keep up the good work!"
                },
                {
                    id: 2,
                    name: "Local Business",
                    amount: 25000,
                    date: "2024-02-20",
                    type: "Education",
                    message: ""
                }
            ];
            localStorage.setItem(KEYS.DONORS, JSON.stringify(defaultDonors));
        }
    }

    // ========== CERTIFICATIONS CRUD ==========
    const Certifications = {
        getAll: function() {
            return JSON.parse(localStorage.getItem(KEYS.CERTIFICATIONS) || '[]');
        },
        
        getById: function(id) {
            const certs = this.getAll();
            return certs.find(c => c.id === parseInt(id));
        },
        
        create: function(certData) {
            const certs = this.getAll();
            const newCert = {
                id: Date.now(),
                title: certData.title,
                description: certData.description,
                pdfUrl: certData.pdfUrl,
                issueDate: certData.issueDate,
                icon: certData.icon || "📜"
            };
            certs.push(newCert);
            localStorage.setItem(KEYS.CERTIFICATIONS, JSON.stringify(certs));
            return { success: true, data: newCert };
        },
        
        update: function(id, certData) {
            const certs = this.getAll();
            const index = certs.findIndex(c => c.id === parseInt(id));
            if (index === -1) return { success: false, error: 'Certificate not found' };
            
            certs[index] = {
                ...certs[index],
                title: certData.title,
                description: certData.description,
                pdfUrl: certData.pdfUrl,
                issueDate: certData.issueDate,
                icon: certData.icon
            };
            localStorage.setItem(KEYS.CERTIFICATIONS, JSON.stringify(certs));
            return { success: true, data: certs[index] };
        },
        
        delete: function(id) {
            const certs = this.getAll();
            const filtered = certs.filter(c => c.id !== parseInt(id));
            localStorage.setItem(KEYS.CERTIFICATIONS, JSON.stringify(filtered));
            return { success: true };
        }
    };

    // ========== TEAM CRUD ==========
    const Team = {
        getAll: function() {
            return JSON.parse(localStorage.getItem(KEYS.TEAM) || '[]');
        },
        
        getById: function(id) {
            const team = this.getAll();
            return team.find(m => m.id === parseInt(id));
        },
        
        create: function(memberData) {
            const team = this.getAll();
            const newMember = {
                id: Date.now(),
                name: memberData.name,
                position: memberData.position,
                description: memberData.description,
                image: memberData.image,
                facebook: memberData.facebook || "#",
                phone: memberData.phone || "",
                twitter: memberData.twitter || "#"
            };
            team.push(newMember);
            localStorage.setItem(KEYS.TEAM, JSON.stringify(team));
            return { success: true, data: newMember };
        },
        
        update: function(id, memberData) {
            const team = this.getAll();
            const index = team.findIndex(m => m.id === parseInt(id));
            if (index === -1) return { success: false, error: 'Team member not found' };
            
            team[index] = {
                ...team[index],
                name: memberData.name,
                position: memberData.position,
                description: memberData.description,
                image: memberData.image,
                facebook: memberData.facebook,
                phone: memberData.phone,
                twitter: memberData.twitter
            };
            localStorage.setItem(KEYS.TEAM, JSON.stringify(team));
            return { success: true, data: team[index] };
        },
        
        delete: function(id) {
            const team = this.getAll();
            const filtered = team.filter(m => m.id !== parseInt(id));
            localStorage.setItem(KEYS.TEAM, JSON.stringify(filtered));
            return { success: true };
        }
    };

    // ========== GALLERY CRUD ==========
    const Gallery = {
        getAll: function() {
            return JSON.parse(localStorage.getItem(KEYS.GALLERY) || '[]');
        },
        
        getById: function(id) {
            const gallery = this.getAll();
            return gallery.find(i => i.id === parseInt(id));
        },
        
        create: function(itemData) {
            const gallery = this.getAll();
            const newItem = {
                id: Date.now(),
                title: itemData.title,
                description: itemData.description,
                image: itemData.image,
                category: itemData.category || 'events',
                date: itemData.date || new Date().toISOString().split('T')[0]
            };
            gallery.push(newItem);
            localStorage.setItem(KEYS.GALLERY, JSON.stringify(gallery));
            return { success: true, data: newItem };
        },
        
        update: function(id, itemData) {
            const gallery = this.getAll();
            const index = gallery.findIndex(i => i.id === parseInt(id));
            if (index === -1) return { success: false, error: 'Gallery item not found' };
            
            gallery[index] = {
                ...gallery[index],
                title: itemData.title,
                description: itemData.description,
                image: itemData.image,
                category: itemData.category,
                date: itemData.date
            };
            localStorage.setItem(KEYS.GALLERY, JSON.stringify(gallery));
            return { success: true, data: gallery[index] };
        },
        
        delete: function(id) {
            const gallery = this.getAll();
            const filtered = gallery.filter(i => i.id !== parseInt(id));
            localStorage.setItem(KEYS.GALLERY, JSON.stringify(filtered));
            return { success: true };
        }
    };

    // ========== DONORS CRUD ==========
    const Donors = {
        getAll: function() {
            return JSON.parse(localStorage.getItem(KEYS.DONORS) || '[]');
        },
        
        getById: function(id) {
            const donors = this.getAll();
            return donors.find(d => d.id === parseInt(id));
        },
        
        create: function(donorData) {
            const donors = this.getAll();
            const newDonor = {
                id: Date.now(),
                name: donorData.name,
                amount: parseFloat(donorData.amount),
                date: donorData.date,
                type: donorData.type || 'General Fund',
                message: donorData.message || ''
            };
            donors.push(newDonor);
            localStorage.setItem(KEYS.DONORS, JSON.stringify(donors));
            return { success: true, data: newDonor };
        },
        
        update: function(id, donorData) {
            const donors = this.getAll();
            const index = donors.findIndex(d => d.id === parseInt(id));
            if (index === -1) return { success: false, error: 'Donor not found' };
            
            donors[index] = {
                ...donors[index],
                name: donorData.name,
                amount: parseFloat(donorData.amount),
                date: donorData.date,
                type: donorData.type,
                message: donorData.message
            };
            localStorage.setItem(KEYS.DONORS, JSON.stringify(donors));
            return { success: true, data: donors[index] };
        },
        
        delete: function(id) {
            const donors = this.getAll();
            const filtered = donors.filter(d => d.id !== parseInt(id));
            localStorage.setItem(KEYS.DONORS, JSON.stringify(filtered));
            return { success: true };
        }
    };

    // ========== NEWS/BLOG CRUD OPERATIONS ==========
    const News = {
        getAll: function() {
            const news = localStorage.getItem(KEYS.NEWS);
            return news ? JSON.parse(news) : [];
        },
        
        getById: function(id) {
            const news = this.getAll();
            return news.find(n => n.id === parseInt(id));
        },
        
        create: function(data) {
            const news = this.getAll();
            const newPost = {
                id: Date.now(),
                title: data.title,
                excerpt: data.excerpt,
                content: data.content,
                image: data.image,
                author: data.author,
                category: data.category,
                date: data.date,
                createdAt: new Date().toISOString()
            };
            news.unshift(newPost);
            localStorage.setItem(KEYS.NEWS, JSON.stringify(news));
            return { success: true, data: newPost };
        },
        
        update: function(id, data) {
            const news = this.getAll();
            const index = news.findIndex(n => n.id === parseInt(id));
            if (index === -1) {
                return { success: false, error: 'News post not found' };
            }
            news[index] = { 
                ...news[index], 
                ...data,
                updatedAt: new Date().toISOString()
            };
            localStorage.setItem(KEYS.NEWS, JSON.stringify(news));
            return { success: true, data: news[index] };
        },
        
        delete: function(id) {
            const news = this.getAll();
            const filtered = news.filter(n => n.id !== parseInt(id));
            localStorage.setItem(KEYS.NEWS, JSON.stringify(filtered));
            return { success: true };
        }
    };

    // ========== IMAGE UPLOAD HANDLER ==========
    function handleImageUpload(inputElement, callback) {
        if (inputElement.files && inputElement.files[0]) {
            const file = inputElement.files[0];
            
            // Validate file type
            if (!file.type.match('image.*')) {
                alert('Please select an image file');
                return;
            }
            
            // Validate file size (max 2MB)
            if (file.size > 2 * 1024 * 1024) {
                alert('Image size should be less than 2MB');
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                callback(e.target.result);
            };
            reader.readAsDataURL(file);
        }
    }

    // Public API
    return {
        init: init,
        Certifications: Certifications,
        Team: Team,
        Gallery: Gallery,
        Donors: Donors,
        News: News,
        handleImageUpload: handleImageUpload
    };
})();

// Initialize on load
if (typeof document !== 'undefined') {
    ContentManager.init();
}
