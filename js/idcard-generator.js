/**
 * ID Card Generator - Pure JavaScript Implementation
 * Generates professional ID cards using HTML5 Canvas
 */

class IDCardGenerator {
    constructor(canvasId) {
        this.canvas = document.getElementById(canvasId);
        this.ctx = this.canvas ? this.canvas.getContext('2d') : null;
        
        // ID Card dimensions (standard credit card size ratio)
        this.width = 800;
        this.height = 500;
        
        if (this.canvas) {
            this.canvas.width = this.width;
            this.canvas.height = this.height;
        }
        
        // Colors
        this.colors = {
            primary: '#2C3E50',
            secondary: '#3498DB',
            accent: '#E74C3C',
            text: '#2C3E50',
            lightText: '#7F8C8D',
            white: '#FFFFFF',
            lightBg: '#ECF0F1'
        };
    }

    /**
     * Generate ID card for a member
     */
    async generateIDCard(memberData) {
        if (!this.ctx) {
            throw new Error('Canvas not initialized');
        }

        // Clear canvas
        this.ctx.clearRect(0, 0, this.width, this.height);

        // Draw background
        this.drawBackground();

        // Draw header
        this.drawHeader();

        // Draw photo placeholder or actual photo
        await this.drawPhoto(memberData.photo);

        // Draw member details
        this.drawMemberDetails(memberData);

        // Draw footer
        this.drawFooter(memberData.userId);

        // Draw QR code placeholder
        this.drawQRCodePlaceholder(memberData.userId);

        return this.canvas;
    }

    /**
     * Draw background with gradient
     */
    drawBackground() {
        // Main background
        this.ctx.fillStyle = this.colors.white;
        this.roundRect(0, 0, this.width, this.height, 20, true, false);

        // Top accent bar
        const gradient = this.ctx.createLinearGradient(0, 0, this.width, 0);
        gradient.addColorStop(0, this.colors.secondary);
        gradient.addColorStop(1, this.colors.primary);
        this.ctx.fillStyle = gradient;
        this.ctx.fillRect(0, 0, this.width, 80);

        // Bottom accent
        this.ctx.fillStyle = this.colors.lightBg;
        this.ctx.fillRect(0, this.height - 60, this.width, 60);
    }

    /**
     * Draw header with organization name
     */
    drawHeader() {
        // Organization name
        this.ctx.fillStyle = this.colors.white;
        this.ctx.font = 'bold 32px Arial, sans-serif';
        this.ctx.textAlign = 'center';
        this.ctx.fillText('SNJ ANKALYAN', this.width / 2, 45);

        // Subtitle
        this.ctx.font = '16px Arial, sans-serif';
        this.ctx.fillText('Member ID Card', this.width / 2, 65);
    }

    /**
     * Draw photo (actual image or placeholder)
     */
    async drawPhoto(photoUrl) {
        const x = 50;
        const y = 110;
        const size = 150;

        // Draw photo border
        this.ctx.strokeStyle = this.colors.secondary;
        this.ctx.lineWidth = 4;
        this.ctx.strokeRect(x - 2, y - 2, size + 4, size + 4);

        if (photoUrl && photoUrl !== '') {
            try {
                const img = await this.loadImage(photoUrl);
                this.ctx.drawImage(img, x, y, size, size);
            } catch (error) {
                console.error('Error loading photo:', error);
                this.drawPhotoPlaceholder(x, y, size);
            }
        } else {
            this.drawPhotoPlaceholder(x, y, size);
        }
    }

    /**
     * Draw photo placeholder
     */
    drawPhotoPlaceholder(x, y, size) {
        this.ctx.fillStyle = this.colors.lightBg;
        this.ctx.fillRect(x, y, size, size);

        // Draw person icon
        this.ctx.fillStyle = this.colors.lightText;
        this.ctx.font = '60px Arial, sans-serif';
        this.ctx.textAlign = 'center';
        this.ctx.fillText('👤', x + size / 2, y + size / 2 + 20);
    }

    /**
     * Draw member details
     */
    drawMemberDetails(member) {
        const leftCol = 230;
        let y = 130;
        const lineHeight = 35;

        this.ctx.textAlign = 'left';

        // Name (larger, bold)
        this.ctx.fillStyle = this.colors.primary;
        this.ctx.font = 'bold 28px Arial, sans-serif';
        this.ctx.fillText(member.name || 'N/A', leftCol, y);
        y += lineHeight + 10;

        // Details
        const details = [
            { label: 'S/O, D/O, W/O:', value: member.sonof || 'N/A' },
            { label: 'Date of Birth:', value: this.formatDate(member.dob) },
            { label: 'Blood Group:', value: member.bloodGroup || 'N/A' },
            { label: 'Gender:', value: member.gender || 'N/A' },
            { label: 'Mobile:', value: member.mobile || 'N/A' },
            { label: 'Address:', value: this.truncateText(member.address || 'N/A', 40) }
        ];

        this.ctx.font = '16px Arial, sans-serif';
        details.forEach(detail => {
            // Label
            this.ctx.fillStyle = this.colors.lightText;
            this.ctx.fillText(detail.label, leftCol, y);

            // Value
            this.ctx.fillStyle = this.colors.text;
            this.ctx.font = 'bold 16px Arial, sans-serif';
            this.ctx.fillText(detail.value, leftCol + 130, y);
            this.ctx.font = '16px Arial, sans-serif';

            y += lineHeight;
        });
    }

    /**
     * Draw footer with User ID
     */
    drawFooter(userId) {
        const y = this.height - 30;

        // User ID badge
        this.ctx.fillStyle = this.colors.accent;
        this.roundRect(this.width / 2 - 100, y - 25, 200, 40, 20, true, false);

        this.ctx.fillStyle = this.colors.white;
        this.ctx.font = 'bold 20px Arial, sans-serif';
        this.ctx.textAlign = 'center';
        this.ctx.fillText(`ID: ${userId}`, this.width / 2, y);

        // Issue date
        this.ctx.fillStyle = this.colors.lightText;
        this.ctx.font = '12px Arial, sans-serif';
        this.ctx.fillText(`Issued: ${this.formatDate(new Date())}`, this.width - 100, this.height - 10);
    }

    /**
     * Draw QR code placeholder
     */
    drawQRCodePlaceholder(userId) {
        const size = 100;
        const x = this.width - size - 50;
        const y = 110;

        // QR background
        this.ctx.fillStyle = this.colors.white;
        this.ctx.fillRect(x, y, size, size);

        // Border
        this.ctx.strokeStyle = this.colors.secondary;
        this.ctx.lineWidth = 2;
        this.ctx.strokeRect(x, y, size, size);

        // QR pattern simulation
        this.ctx.fillStyle = this.colors.text;
        const cellSize = 10;
        for (let i = 0; i < size; i += cellSize) {
            for (let j = 0; j < size; j += cellSize) {
                if (Math.random() > 0.5) {
                    this.ctx.fillRect(x + i, y + j, cellSize - 1, cellSize - 1);
                }
            }
        }

        // QR label
        this.ctx.fillStyle = this.colors.lightText;
        this.ctx.font = '10px Arial, sans-serif';
        this.ctx.textAlign = 'center';
        this.ctx.fillText('Scan for verification', x + size / 2, y + size + 15);
    }

    /**
     * Helper: Round rectangle
     */
    roundRect(x, y, width, height, radius, fill, stroke) {
        this.ctx.beginPath();
        this.ctx.moveTo(x + radius, y);
        this.ctx.lineTo(x + width - radius, y);
        this.ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
        this.ctx.lineTo(x + width, y + height - radius);
        this.ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
        this.ctx.lineTo(x + radius, y + height);
        this.ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
        this.ctx.lineTo(x, y + radius);
        this.ctx.quadraticCurveTo(x, y, x + radius, y);
        this.ctx.closePath();
        if (fill) this.ctx.fill();
        if (stroke) this.ctx.stroke();
    }

    /**
     * Helper: Load image
     */
    loadImage(url) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.onload = () => resolve(img);
            img.onerror = reject;
            img.crossOrigin = 'anonymous';
            img.src = url;
        });
    }

    /**
     * Helper: Format date
     */
    formatDate(date) {
        if (!date) return 'N/A';
        const d = typeof date === 'string' ? new Date(date) : date;
        return d.toLocaleDateString('en-IN', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }

    /**
     * Helper: Truncate text
     */
    truncateText(text, maxLength) {
        if (text.length <= maxLength) return text;
        return text.substring(0, maxLength - 3) + '...';
    }

    /**
     * Download ID card as image
     */
    downloadIDCard(filename = 'id-card.png') {
        if (!this.canvas) {
            throw new Error('Canvas not initialized');
        }

        const link = document.createElement('a');
        link.download = filename;
        link.href = this.canvas.toDataURL('image/png');
        link.click();
    }

    /**
     * Get ID card as data URL
     */
    getDataURL() {
        if (!this.canvas) {
            throw new Error('Canvas not initialized');
        }
        return this.canvas.toDataURL('image/png');
    }
}
