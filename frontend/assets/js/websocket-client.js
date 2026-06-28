// ============================================================
// GeoTraverse WebSocket Client - Reusable for ALL Dashboards
// ============================================================

class GeoTraverseNotificationClient {
    constructor(options = {}) {
        this.socket = null;
        this.departmentId = options.departmentId || this.getDepartmentId();
        this.departmentName = options.departmentName || this.getDepartmentName();
        this.userName = options.userName || this.getUserName() || 'User';
        this.wsUrl = options.wsUrl || 'ws://localhost:3001';
        
        this.isConnected = false;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 10;
        this.reconnectDelay = 3000;
        
        // Callbacks
        this.onNotification = options.onNotification || null;
        this.onUnreadCount = options.onUnreadCount || null;
        this.onConnect = options.onConnect || null;
        this.onDisconnect = options.onDisconnect || null;
        
        // DOM Elements
        this.badgeElement = options.badgeElement || document.getElementById('notificationBadge');
        this.notificationContainer = options.notificationContainer || document.getElementById('notificationContainer');
        this.notificationSound = options.notificationSound !== undefined ? options.notificationSound : true;
        this.desktopNotifications = options.desktopNotifications !== undefined ? options.desktopNotifications : true;
        
        // Audio context for sound
        this.audioContext = null;
        
        // Initialize
        this.init();
    }
    
    // ============================================================
    // GET DEPARTMENT ID FROM PAGE / SESSION
    // ============================================================
    getDepartmentId() {
        // Try sessionStorage first
        const sessionId = sessionStorage.getItem('department_id');
        if (sessionId) return parseInt(sessionId);
        
        // Try from page - check for department ID in URL or meta
        const metaDept = document.querySelector('meta[name="department-id"]');
        if (metaDept) return parseInt(metaDept.getAttribute('content'));
        
        // Default based on page name
        const pageName = window.location.pathname.split('/').pop().replace('.html', '');
        const deptMap = {
            'super_admin': 1,
            'finance': 2,
            'sales_marketing': 3,
            'manager': 4,
            'secretary': 5,
            'bricks_timber': 6,
            'aluminium': 7,
            'town_planning': 8,
            'architectural': 9,
            'survey': 10,
            'construction': 11,
            'hatimiliki': 12
        };
        return deptMap[pageName] || 1;
    }
    
    // ============================================================
    // GET DEPARTMENT NAME
    // ============================================================
    getDepartmentName() {
        const deptMap = {
            1: 'Super Admin',
            2: 'Finance',
            3: 'Sales & Marketing',
            4: 'Manager',
            5: 'Secretary',
            6: 'Bricks & Timber',
            7: 'Aluminium',
            8: 'Town Planning',
            9: 'Architectural',
            10: 'Survey',
            11: 'Construction',
            12: 'Hatimiliki'
        };
        return deptMap[this.getDepartmentId()] || 'Unknown';
    }
    
    // ============================================================
    // GET USER NAME
    // ============================================================
    getUserName() {
        return sessionStorage.getItem('user_name') || 
               localStorage.getItem('user_name') || 
               document.querySelector('meta[name="user-name"]')?.getAttribute('content') || 
               this.getDepartmentName();
    }
    
    // ============================================================
    // INITIALIZE
    // ============================================================
    init() {
        console.log(`🔔 Initializing notifications for ${this.departmentName} (Dept ${this.departmentId})`);
        this.connect();
        this.requestNotificationPermission();
    }
    
    // ============================================================
    // CONNECT TO WEBSOCKET
    // ============================================================
    connect() {
        try {
            // Check if Socket.io is loaded
            if (typeof io === 'undefined') {
                console.warn('⚠️ Socket.io not loaded, retrying...');
                setTimeout(() => this.connect(), 2000);
                return;
            }
            
            console.log('🔌 Connecting to WebSocket:', this.wsUrl);
            
            this.socket = io(this.wsUrl, {
                transports: ['websocket', 'polling'],
                reconnection: false,
                timeout: 5000
            });
            
            // ============================================================
            // SOCKET EVENTS
            // ============================================================
            
            this.socket.on('connect', () => {
                console.log('✅ WebSocket connected!');
                this.isConnected = true;
                this.reconnectAttempts = 0;
                
                // Register user
                this.socket.emit('register', {
                    departmentId: this.departmentId,
                    userName: this.userName,
                    departmentName: this.departmentName
                });
                
                this.showToast('🔔 Real-time notifications connected', 'success');
                if (this.onConnect) this.onConnect();
            });
            
            this.socket.on('registered', (data) => {
                console.log('✅ Registered:', data);
            });
            
            // ============================================================
            // NEW NOTIFICATION - MAIN EVENT
            // ============================================================
            this.socket.on('new_notification', (data) => {
                console.log('🔔 New notification:', data);
                
                // Play sound
                if (this.notificationSound) {
                    this.playSound();
                }
                
                // Show desktop notification
                if (this.desktopNotifications && 'Notification' in window && Notification.permission === 'granted') {
                    this.showDesktopNotification(data);
                }
                
                // Show toast
                this.showToastFromData(data);
                
                // Update badge
                this.updateBadge();
                
                // Call custom callback
                if (this.onNotification) {
                    this.onNotification(data);
                }
                
                // Dispatch custom event for page-specific handling
                const event = new CustomEvent('geotraverse-notification', { detail: data });
                document.dispatchEvent(event);
            });
            
            // ============================================================
            // UNREAD COUNT UPDATE
            // ============================================================
            this.socket.on('unread_count', (data) => {
                console.log('📊 Unread count:', data.count);
                this.updateBadge(data.count);
                if (this.onUnreadCount) {
                    this.onUnreadCount(data.count);
                }
                // Dispatch event for page to update
                const event = new CustomEvent('geotraverse-unread-count', { detail: data });
                document.dispatchEvent(event);
            });
            
            // ============================================================
            // RECENT NOTIFICATIONS
            // ============================================================
            this.socket.on('recent_notifications', (data) => {
                console.log('📋 Recent notifications:', data.data?.length || 0);
                if (this.onRecentNotifications) {
                    this.onRecentNotifications(data.data);
                }
                this.renderNotifications(data.data);
            });
            
            // ============================================================
            // DISCONNECT & RECONNECT
            // ============================================================
            this.socket.on('disconnect', () => {
                console.log('❌ WebSocket disconnected');
                this.isConnected = false;
                if (this.onDisconnect) this.onDisconnect();
                this.reconnect();
            });
            
            this.socket.on('connect_error', (error) => {
                console.error('❌ Connection error:', error);
                this.reconnect();
            });
            
        } catch (error) {
            console.error('❌ WebSocket error:', error);
            this.reconnect();
        }
    }
    
    // ============================================================
    // RECONNECT
    // ============================================================
    reconnect() {
        if (this.reconnectAttempts >= this.maxReconnectAttempts) {
            console.log('❌ Max reconnect attempts reached');
            return;
        }
        
        this.reconnectAttempts++;
        const delay = this.reconnectDelay * Math.min(this.reconnectAttempts, 5);
        
        console.log(`🔄 Reconnecting in ${delay}ms (attempt ${this.reconnectAttempts}/${this.maxReconnectAttempts})`);
        
        setTimeout(() => {
            if (!this.isConnected) {
                this.connect();
            }
        }, delay);
    }
    
    // ============================================================
    // REQUEST NOTIFICATION PERMISSION
    // ============================================================
    requestNotificationPermission() {
        if (!('Notification' in window)) return;
        
        if (Notification.permission === 'default') {
            Notification.requestPermission().then(permission => {
                console.log('📢 Notification permission:', permission);
            });
        }
    }
    
    // ============================================================
    // PLAY NOTIFICATION SOUND
    // ============================================================
    playSound() {
        try {
            if (!this.audioContext) {
                this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
            }
            
            // Create two-tone notification sound
            const frequencies = [880, 1100];
            const duration = 120;
            const gap = 80;
            
            frequencies.forEach((freq, index) => {
                setTimeout(() => {
                    try {
                        const oscillator = this.audioContext.createOscillator();
                        const gainNode = this.audioContext.createGain();
                        
                        oscillator.connect(gainNode);
                        gainNode.connect(this.audioContext.destination);
                        
                        oscillator.frequency.value = freq;
                        oscillator.type = 'sine';
                        gainNode.gain.value = 0.15;
                        
                        oscillator.start();
                        setTimeout(() => oscillator.stop(), duration);
                    } catch(e) {}
                }, index * (duration + gap));
            });
            
        } catch (error) {
            // Silent fail - audio not required
        }
    }
    
    // ============================================================
    // SHOW DESKTOP NOTIFICATION
    // ============================================================
    showDesktopNotification(data) {
        try {
            if (!('Notification' in window) || Notification.permission !== 'granted') return;
            
            const iconMap = {
                'project': '📋',
                'report': '📊',
                'uploaded_report': '📄',
                'document': '📁',
                'fund_request': '💰',
                'dailywork': '📅',
                'employee': '👤',
                'visitor': '🚶'
            };
            const icon = iconMap[data.item_type] || '🔔';
            
            const notification = new Notification(`${icon} ${data.item_title || 'New Notification'}`, {
                body: data.message || 'You have a new notification',
                icon: 'https://i.postimg.cc/MT6jTVHh/weblogo.png'
            });
            
            notification.onclick = () => {
                window.focus();
                if (data.action_url) {
                    window.location.href = data.action_url;
                }
                notification.close();
                // Mark as read when clicked
                this.markRead(data.id);
            };
            
            setTimeout(() => notification.close(), 8000);
            
        } catch (error) {
            console.error('Desktop notification error:', error);
        }
    }
    
    // ============================================================
    // SHOW TOAST NOTIFICATION
    // ============================================================
    showToastFromData(data) {
        const iconMap = {
            'project': '📋',
            'report': '📊',
            'uploaded_report': '📄',
            'document': '📁',
            'fund_request': '💰',
            'dailywork': '📅',
            'employee': '👤',
            'visitor': '🚶'
        };
        const icon = iconMap[data.item_type] || '🔔';
        const title = data.item_title || 'New Notification';
        const message = data.message || '';
        
        this.showToast(`${icon} ${title}: ${message}`, 'info');
    }
    
    // ============================================================
    // SHOW TOAST (Simple)
    // ============================================================
    showToast(message, type = 'info') {
        // Check if global showToast exists (from dashboard)
        if (typeof window.showToast === 'function') {
            window.showToast(message, type === 'error');
            return;
        }
        
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `toast-notification ${type === 'error' ? 'error' : ''}`;
        toast.innerHTML = `<i class="fas ${type === 'error' ? 'fa-exclamation-circle' : 'fa-bell'} mr-2"></i> ${message}`;
        toast.style.position = 'fixed';
        toast.style.bottom = '20px';
        toast.style.right = '20px';
        toast.style.background = type === 'error' ? '#ef4444' : '#10b981';
        toast.style.color = 'white';
        toast.style.padding = '12px 20px';
        toast.style.borderRadius = '10px';
        toast.style.zIndex = '10000';
        toast.style.animation = 'slideInRight 0.3s ease';
        toast.style.fontSize = '13px';
        toast.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
        toast.style.maxWidth = '350px';
        
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 5000);
    }
    
    // ============================================================
    // UPDATE BADGE
    // ============================================================
    updateBadge(count = null) {
        if (!this.badgeElement) return;
        
        if (count === null) {
            // Fetch from API
            fetch(`/geotraverse/backend/api/get_unread_count.php?department_id=${this.departmentId}`)
                .then(res => res.json())
                .then(data => {
                    this.updateBadgeUI(data.count || 0);
                })
                .catch(() => {});
            return;
        }
        
        this.updateBadgeUI(count);
    }
    
    updateBadgeUI(count) {
        if (!this.badgeElement) return;
        
        if (count > 0) {
            this.badgeElement.textContent = count > 99 ? '99+' : count;
            this.badgeElement.classList.remove('hidden');
            this.badgeElement.style.display = 'block';
        } else {
            this.badgeElement.classList.add('hidden');
            this.badgeElement.style.display = 'none';
        }
    }
    
    // ============================================================
    // MARK NOTIFICATION AS READ
    // ============================================================
    markRead(notificationId) {
        if (this.socket && this.isConnected) {
            this.socket.emit('mark_read', {
                notificationId: notificationId,
                departmentId: this.departmentId
            });
        }
    }
    
    // ============================================================
    // MARK ALL AS READ
    // ============================================================
    markAllRead() {
        if (this.socket && this.isConnected) {
            this.socket.emit('mark_all_read', {
                departmentId: this.departmentId
            });
        }
    }
    
    // ============================================================
    // GET NOTIFICATIONS
    // ============================================================
    getNotifications(limit = 50, offset = 0) {
        if (this.socket && this.isConnected) {
            this.socket.emit('get_notifications', {
                departmentId: this.departmentId,
                limit: limit,
                offset: offset
            });
        }
    }
    
    // ============================================================
    // RENDER NOTIFICATIONS IN CONTAINER
    // ============================================================
    renderNotifications(notifications) {
        if (!this.notificationContainer || !notifications) return;
        
        if (notifications.length === 0) {
            this.notificationContainer.innerHTML = `
                <div class="text-center py-8 text-gray-400">
                    <i class="fas fa-bell-slash text-2xl mb-2"></i>
                    <p>No notifications</p>
                </div>
            `;
            return;
        }
        
        let html = '';
        notifications.forEach(n => {
            const isRead = n.is_read == 1;
            const iconMap = {
                'project': 'fa-tasks',
                'report': 'fa-chart-line',
                'uploaded_report': 'fa-file-upload',
                'document': 'fa-file-alt',
                'fund_request': 'fa-hand-holding-usd',
                'dailywork': 'fa-calendar-day',
                'employee': 'fa-user'
            };
            const icon = iconMap[n.item_type] || 'fa-bell';
            
            html += `
                <div class="notification-item ${!isRead ? 'unread' : ''}" 
                     data-id="${n.id}"
                     onclick="window.notificationClient?.markRead(${n.id})"
                     style="padding: 12px 16px; border-bottom: 1px solid #e5e7eb; cursor: pointer; ${!isRead ? 'background: #fef3c7;' : ''}">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                            <i class="fas ${icon} text-blue-600 text-sm"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between items-start">
                                <p class="font-semibold text-sm ${!isRead ? 'text-gray-900' : 'text-gray-600'}">
                                    ${n.item_title || 'Notification'}
                                </p>
                                <span class="text-[10px] text-gray-400 whitespace-nowrap ml-2">
                                    ${new Date(n.created_at).toLocaleTimeString()}
                                </span>
                            </div>
                            <p class="text-sm text-gray-500">${n.message || ''}</p>
                            ${n.from_department_name ? 
                                `<p class="text-[10px] text-gray-400 mt-1">From: ${n.from_department_name}</p>` : ''}
                            ${!isRead ? 
                                '<span class="text-[9px] bg-red-500 text-white px-2 py-0.5 rounded-full mt-1 inline-block">NEW</span>' : ''}
                        </div>
                    </div>
                </div>
            `;
        });
        
        this.notificationContainer.innerHTML = html;
    }
}

// ============================================================
// EXPOSE GLOBALLY
// ============================================================
window.GeoTraverseNotificationClient = GeoTraverseNotificationClient;