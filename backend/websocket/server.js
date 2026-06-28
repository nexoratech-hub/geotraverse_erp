// ============================================================
// GeoTraverse WebSocket Server - Real-time Notifications
// ============================================================

const express = require('express');
const http = require('http');
const socketIo = require('socket.io');
const cors = require('cors');
const mysql = require('mysql2/promise');
require('dotenv').config();

const app = express();
const server = http.createServer(app);

// CORS configuration
const io = socketIo(server, {
    cors: {
        origin: "*",
        methods: ["GET", "POST"],
        credentials: true
    },
    transports: ['websocket', 'polling']
});

// Database connection pool
const pool = mysql.createPool({
    host: process.env.DB_HOST || 'localhost',
    user: process.env.DB_USER || 'root',
    password: process.env.DB_PASSWORD || '',
    database: process.env.DB_NAME || 'geotraverse',
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
});

// ============================================================
// STORE CONNECTED USERS
// ============================================================
const connectedUsers = new Map();

// ============================================================
// SOCKET.IO EVENTS
// ============================================================
io.on('connection', (socket) => {
    console.log('🔌 New client connected:', socket.id);

    // REGISTER USER
    socket.on('register', async (data) => {
        try {
            const { departmentId, userName } = data;
            
            connectedUsers.set(socket.id, {
                departmentId: parseInt(departmentId),
                userName: userName || 'Unknown',
                connectedAt: new Date()
            });

            socket.join(`dept_${departmentId}`);
            
            console.log(`✅ User "${userName}" (Dept ${departmentId}) registered`);
            
            // Send unread notifications count
            const unreadCount = await getUnreadCount(departmentId);
            socket.emit('unread_count', { count: unreadCount });
            
            // Send recent notifications (last 50)
            const recentNotifications = await getRecentNotifications(departmentId, 50);
            socket.emit('recent_notifications', { data: recentNotifications });
            
            socket.emit('registered', { 
                success: true, 
                departmentId: departmentId,
                userName: userName
            });

        } catch (error) {
            console.error('❌ Register error:', error);
            socket.emit('error', { message: 'Registration failed' });
        }
    });

    // MARK NOTIFICATION AS READ
    socket.on('mark_read', async (data) => {
        try {
            const { notificationId, departmentId } = data;
            
            const [result] = await pool.execute(
                `UPDATE notifications 
                 SET is_read = 1, read_at = NOW() 
                 WHERE id = ? AND department_id = ?`,
                [notificationId, departmentId]
            );
            
            if (result.affectedRows > 0) {
                const unreadCount = await getUnreadCount(departmentId);
                io.to(`dept_${departmentId}`).emit('unread_count', { count: unreadCount });
                socket.emit('mark_success', { notificationId });
            }
        } catch (error) {
            console.error('❌ Mark read error:', error);
        }
    });

    // MARK ALL AS READ
    socket.on('mark_all_read', async (data) => {
        try {
            const { departmentId } = data;
            
            await pool.execute(
                `UPDATE notifications 
                 SET is_read = 1, read_at = NOW() 
                 WHERE department_id = ? AND is_read = 0`,
                [departmentId]
            );
            
            const unreadCount = await getUnreadCount(departmentId);
            io.to(`dept_${departmentId}`).emit('unread_count', { count: 0 });
            socket.emit('mark_all_success');
            
        } catch (error) {
            console.error('❌ Mark all read error:', error);
        }
    });

    // DISCONNECT
    socket.on('disconnect', () => {
        const user = connectedUsers.get(socket.id);
        if (user) {
            console.log(`🔌 User "${user.userName}" (Dept ${user.departmentId}) disconnected`);
            connectedUsers.delete(socket.id);
        }
    });
});

// ============================================================
// HELPER FUNCTIONS - DATABASE
// ============================================================

async function getUnreadCount(departmentId) {
    try {
        const [rows] = await pool.execute(
            `SELECT COUNT(*) as count 
             FROM notifications 
             WHERE department_id = ? AND is_read = 0`,
            [departmentId]
        );
        return rows[0]?.count || 0;
    } catch (error) {
        console.error('❌ Get unread count error:', error);
        return 0;
    }
}

async function getRecentNotifications(departmentId, limit = 50, offset = 0) {
    try {
        const [rows] = await pool.execute(
            `SELECT n.*, d.name as from_department_name
             FROM notifications n
             LEFT JOIN departments d ON n.from_department_id = d.id
             WHERE n.department_id = ?
             ORDER BY n.created_at DESC
             LIMIT ? OFFSET ?`,
            [departmentId, parseInt(limit), parseInt(offset)]
        );
        return rows;
    } catch (error) {
        console.error('❌ Get recent notifications error:', error);
        return [];
    }
}

// ============================================================
// SEND NOTIFICATION TO DEPARTMENT
// ============================================================
async function sendNotificationToDepartment(departmentId, data) {
    try {
        console.log(`📝 Sending notification to Dept ${departmentId}:`, data.item_title);
        
        const [result] = await pool.execute(
            `INSERT INTO notifications 
             (department_id, from_department_id, from_department_name, item_type, item_id, item_title, message, action_url) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)`,
            [
                departmentId,
                data.from_department_id || 1,
                data.from_department_name || 'Super Admin',
                data.item_type || 'general',
                data.item_id || 0,
                data.item_title || 'Notification',
                data.message || '',
                data.action_url || ''
            ]
        );
        
        const notificationId = result.insertId;
        
        const notificationData = {
            id: notificationId,
            department_id: departmentId,
            from_department_id: data.from_department_id || 1,
            from_department_name: data.from_department_name || 'Super Admin',
            item_type: data.item_type || 'general',
            item_id: data.item_id || 0,
            item_title: data.item_title || 'Notification',
            message: data.message || '',
            action_url: data.action_url || '',
            is_read: 0,
            created_at: new Date().toISOString()
        };
        
        io.to(`dept_${departmentId}`).emit('new_notification', notificationData);
        
        const unreadCount = await getUnreadCount(departmentId);
        io.to(`dept_${departmentId}`).emit('unread_count', { count: unreadCount });
        
        console.log(`✅ Notification sent to Dept ${departmentId}: ${data.item_title}`);
        return { success: true, notificationId };
        
    } catch (error) {
        console.error('❌ Send notification error:', error);
        console.error('❌ Error details:', {
            message: error.message,
            code: error.code,
            sqlState: error.sqlState,
            sqlMessage: error.sqlMessage
        });
        return { success: false, error: error.message };
    }
}

// ============================================================
// HTTP ENDPOINT FOR PHP TO SEND NOTIFICATIONS
// ============================================================
app.use(express.json());
app.use(cors());

app.post('/send-notification', async (req, res) => {
    try {
        const { department_id, item_type, item_id, item_title, message, action_url, from_department_id, from_department_name } = req.body;
        
        if (!department_id) {
            return res.status(400).json({ success: false, error: 'department_id required' });
        }
        
        const result = await sendNotificationToDepartment(department_id, {
            from_department_id: from_department_id || 1,
            from_department_name: from_department_name || 'Super Admin',
            item_type: item_type || 'general',
            item_id: item_id || 0,
            item_title: item_title || 'Notification',
            message: message || '',
            action_url: action_url || ''
        });
        
        res.json(result);
        
    } catch (error) {
        console.error('❌ HTTP send notification error:', error);
        res.status(500).json({ success: false, error: error.message });
    }
});

// ============================================================
// HTTP ENDPOINT - GET NOTIFICATIONS
// ============================================================
app.get('/notifications/:departmentId', async (req, res) => {
    try {
        const departmentId = parseInt(req.params.departmentId);
        const limit = parseInt(req.query.limit) || 50;
        const offset = parseInt(req.query.offset) || 0;
        
        const notifications = await getRecentNotifications(departmentId, limit, offset);
        const unreadCount = await getUnreadCount(departmentId);
        
        res.json({
            success: true,
            data: notifications,
            unread_count: unreadCount,
            total: notifications.length
        });
        
    } catch (error) {
        console.error('❌ GET notifications error:', error);
        res.status(500).json({ success: false, error: error.message });
    }
});

// ============================================================
// HTTP ENDPOINT - UNREAD COUNT
// ============================================================
app.get('/unread-count/:departmentId', async (req, res) => {
    try {
        const departmentId = parseInt(req.params.departmentId);
        const count = await getUnreadCount(departmentId);
        res.json({ success: true, count });
    } catch (error) {
        console.error('❌ GET unread count error:', error);
        res.status(500).json({ success: false, error: error.message });
    }
});

// ============================================================
// START SERVER
// ============================================================
const PORT = process.env.PORT || 3001;

server.listen(PORT, '0.0.0.0', () => {
    console.log(`🚀 WebSocket Server running on port ${PORT}`);
    console.log(`📡 WebSocket: ws://localhost:${PORT}`);
    console.log(`📡 HTTP API: http://localhost:${PORT}`);
});

module.exports = {
    sendNotificationToDepartment,
    getUnreadCount,
    getRecentNotifications,
    pool
};