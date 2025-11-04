Member Uploads Directory
========================

This directory stores member application data including:
- Profile pictures
- ID documents
- Payment screenshots
- Member data CSV file (submissions.csv)

Structure:
----------
/uploads/members/
  ├── submissions.csv (main database of all member applications)
  └── [Member ID folders]/
      ├── profile_pic.jpg
      ├── id_doc.jpg
      ├── other_doc.jpg
      └── screenshot.jpg

Security:
---------
- .htaccess file protects sensitive files
- Only image files are accessible via web
- CSV file is protected from direct access

Note: This directory is automatically created when the first member registers.
