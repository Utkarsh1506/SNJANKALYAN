# SNJANKALYAN - Complete ID Card Generation System

## Overview
This is a complete member registration and ID card generation system for the SNJANKALYAN organization. Members can register online, and after approval, download their official ID cards.

## System Components

### 1. Member Registration (`member.html` + `member_submit.php`)
**Purpose:** Allows users to register as members of the organization.

**Features:**
- Complete registration form with personal details
- Profile photo upload
- ID document upload
- Mandatory donation with payment screenshot
- UPI QR code for easy payment
- Automatic User ID generation
- Form validation

**How it works:**
1. User fills out the registration form
2. Makes a donation via UPI
3. Uploads payment screenshot and UTR number
4. Submits the form
5. System generates a unique User ID (format: M{timestamp}{random})
6. All data is saved to `uploads/members/submissions.csv`
7. Files are uploaded to `uploads/members/{UserID}/`
8. Application status is set to "pending"

### 2. Admin Panel (`admin_members.php`)
**Purpose:** Admin interface to review and approve/reject member applications.

**Features:**
- View all member applications in a table
- See member photos, details, and payment information
- Approve, reject, or mark applications as pending
- Statistics dashboard showing counts by status
- Sort by newest applications first

**How to use:**
1. Navigate to `admin_members.php` in your browser
2. Review each application details
3. Click the green checkmark to approve
4. Click the red X to reject
5. Click the clock icon to mark as pending

**Note:** In production, add proper authentication to protect this page.

### 3. ID Card Download (`idcard.html` + `idcard.php` + `idcard_data.php`)
**Purpose:** Members can download their ID cards after approval.

**Features:**
- Two methods to get ID card:
  - **Preview (HTML5 Canvas):** Client-side preview before download
  - **Download (PHP GD):** Server-side high-quality PNG generation
- Credential verification (User ID + Date of Birth)
- Status check (only approved members can download)
- Professional ID card design with:
  - Organization branding
  - Member photo
  - Personal details (name, gender, blood group, etc.)
  - Member ID barcode/QR code ready
  - Issue date

**How it works:**
1. Member enters User ID and Date of Birth
2. Click "Preview ID Card" for instant preview
   - Fetches data from `idcard_data.php`
   - Renders ID card using HTML5 Canvas
   - Can download preview directly
3. Click "Download ID Card (PNG)" for server-generated card
   - Validates credentials via `idcard.php`
   - Generates high-quality ID card using PHP GD library
   - Downloads as PNG file

## File Structure

```
SNJANKALYAN/
├── member.html                 # Member registration form
├── member_submit.php           # Processes registration
├── idcard.html                 # ID card download page
├── idcard.php                  # Server-side ID card generator
├── idcard_data.php             # JSON API for member data
├── admin_members.php           # Admin panel for approvals
├── uploads/
│   └── members/
│       ├── submissions.csv     # Member database
│       ├── .htaccess          # Security rules
│       ├── README.txt         # Directory info
│       └── M{timestamp}{id}/  # Individual member folders
│           ├── profile_pic.jpg
│           ├── id_doc.jpg
│           ├── other_doc.jpg
│           └── screenshot.jpg
└── ...other site files
```

## Database Structure (CSV)

### submissions.csv columns:
1. timestamp - Application submission date/time
2. userid - Unique member ID (M{timestamp}{random})
3. name - Member name
4. gender - Male/Female
5. sonof - Father's/Guardian's name
6. dob - Date of birth (used as password)
7. mobile - Mobile number
8. aadhar - Aadhar number
9. email - Email address
10. address - Full address
11. pincode - PIN code
12. profession - Occupation
13. blood - Blood group
14. state - State
15. district - District
16. profile_pic - Profile photo filename
17. id_doc - ID document filename
18. other_doc - Other document filename
19. amount - Donation amount
20. utr - Payment UTR/transaction number
21. screenshot - Payment screenshot filename
22. status - Application status (pending/approved/rejected)

## User Workflow

### For Members:
1. **Register** (member.html)
   - Fill registration form
   - Make donation and upload proof
   - Receive User ID and password (DOB)

2. **Wait for Approval**
   - Application is reviewed by admin
   - Status: pending → approved/rejected

3. **Download ID Card** (idcard.html)
   - Enter User ID and DOB
   - Preview card online
   - Download as PNG image

### For Admins:
1. **Access Admin Panel** (admin_members.php)
   - Review all applications
   - Check payment screenshots
   - Verify member details

2. **Approve/Reject**
   - Click approve for valid applications
   - Click reject for invalid ones
   - View statistics

## ID Card Design Specifications

### Dimensions:
- Width: 1050 pixels (3.5 inches at 300 DPI)
- Height: 600 pixels (2 inches at 300 DPI)
- Standard credit card ratio

### Colors:
- Header Background: #2980b9 (Professional Blue)
- Text Dark: #2c3e50
- Text Light: #7f8c8d
- Border: #bdc3c7

### Content:
- Organization name and logo
- Member photo (180x220px)
- Member details:
  - Name (large, bold)
  - Member ID
  - Gender and Blood Group
  - Date of Birth
  - Mobile Number
  - Full Address
  - Issue Date
  - Website

## Security Features

1. **File Upload Security:**
   - File type validation (only images)
   - File size limits (6MB max)
   - Unique filenames to prevent conflicts
   - Isolated storage per member

2. **Data Protection:**
   - .htaccess prevents direct CSV access
   - Password is DOB (not stored separately)
   - Status check prevents unauthorized downloads

3. **Admin Access:**
   - TODO: Add authentication
   - Currently open - secure in production!

## Installation & Setup

### Requirements:
- PHP 7.4 or higher
- GD Library enabled (for image processing)
- Apache/Nginx web server
- Write permissions on uploads/ directory

### Steps:
1. Upload all files to your web server
2. Ensure `uploads/members/` directory is writable:
   ```bash
   chmod 755 uploads/members
   ```
3. Test member registration at `member.html`
4. Access admin panel at `admin_members.php`
5. Test ID card download at `idcard.html`

### Font Configuration:
The system tries to use TrueType fonts for better ID cards:
- Checks: `fonts/arial.ttf`
- Falls back to system fonts on Windows/Linux/Mac
- If no TTF fonts found, uses built-in GD fonts

### For better quality, add Arial font:
1. Create `fonts/` directory
2. Copy `arial.ttf` to `fonts/arial.ttf`
3. Ensure readable by web server

## Troubleshooting

### Issue: ID card shows "No Photo"
**Solution:** 
- Check if profile photo was uploaded
- Verify file exists in `uploads/members/{UserID}/`
- Check file permissions

### Issue: "Application under review" message
**Solution:**
- Admin needs to approve in admin panel
- Check status in CSV file
- Ensure status is "approved"

### Issue: Fonts look ugly on ID card
**Solution:**
- Add TrueType fonts to `fonts/` directory
- Use arial.ttf, dejavu.ttf, or similar
- Check PHP GD has FreeType support: `php -i | grep -i freetype`

### Issue: CSV file not updating
**Solution:**
- Check directory permissions (755 for directories, 644 for files)
- Verify PHP has write access
- Check server error logs

### Issue: Images not displaying in admin panel
**Solution:**
- Check file paths in CSV
- Verify uploads directory structure
- Check .htaccess isn't blocking images

## API Reference

### idcard_data.php
**Endpoint:** POST to `idcard_data.php`

**Parameters:**
- `action`: "fetch"
- `userid`: Member User ID
- `dob`: Date of Birth (password)

**Response (JSON):**
```json
{
  "success": true,
  "userid": "M1730723456abc123",
  "name": "John Doe",
  "gender": "Male",
  "sonof": "Father Name",
  "dob": "01-01-1990",
  "mobile": "9876543210",
  "blood": "O+",
  "address": "123 Street",
  "state": "Uttarakhand",
  "district": "Pauri Garhwal",
  "pincode": "246164",
  "email": "john@example.com",
  "profession": "Business",
  "profile_pic": "uploads/members/M.../photo.jpg"
}
```

**Error Response:**
```json
{
  "error": "Error message here"
}
```

## Customization

### Change ID Card Design:
Edit `idcard.php` around line 80-200:
- Modify colors in color allocation section
- Adjust layout coordinates
- Change fonts and sizes
- Add organization logo

### Change Registration Fields:
1. Edit `member.html` form fields
2. Update `member_submit.php` to capture new fields
3. Update CSV header in `member_submit.php`
4. Update `idcard.php` to display new fields

### Add Authentication to Admin Panel:
```php
// Add at top of admin_members.php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}
```

## Future Enhancements

1. **Database Migration:**
   - Move from CSV to MySQL/PostgreSQL
   - Better performance and querying
   - Relational data structure

2. **Email Notifications:**
   - Send email when application is approved
   - Email User ID to member
   - Password reset functionality

3. **QR Code on ID Card:**
   - Generate QR code with member ID
   - Scan for verification
   - Link to online profile

4. **Member Portal:**
   - Login system for members
   - View application status
   - Update contact details
   - Download ID card multiple times

5. **Bulk Operations:**
   - Approve multiple applications at once
   - Export member data
   - Generate reports

6. **Search & Filter:**
   - Search members by name, ID, phone
   - Filter by status, date, location
   - Advanced analytics

## Support & Contact

For issues or questions about this system:
- Email: s.nautiyalpabo@gmail.com
- Phone: +91 90583 98880
- Website: www.snjankalyan.org

## License

Copyright © 2025 SNJANKALYAN - Jan Kalyan Samiti
All rights reserved.

---

**Version:** 1.0  
**Last Updated:** November 2025  
**Developed by:** Agnvridhi India
