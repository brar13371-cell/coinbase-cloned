import { createSlice, createAsyncThunk, PayloadAction } from '@reduxjs/toolkit';
import { Notification, ApiResponse } from '../../types';
import { notificationService } from '../../services/notificationService';

interface NotificationState {
  notifications: Notification[];
  unreadCount: number;
  isLoading: boolean;
  error: string | null;
}

const initialState: NotificationState = {
  notifications: [],
  unreadCount: 0,
  isLoading: false,
  error: null,
};

// Async thunks
export const fetchNotifications = createAsyncThunk(
  'notification/fetchNotifications',
  async (_, { rejectWithValue }) => {
    try {
      const response = await notificationService.getNotifications();
      if (response.success) {
        return response.data.notifications;
      } else {
        return rejectWithValue(response.message || 'Failed to fetch notifications');
      }
    } catch (error: any) {
      return rejectWithValue(error.message || 'Failed to fetch notifications');
    }
  }
);

export const fetchUnreadNotifications = createAsyncThunk(
  'notification/fetchUnreadNotifications',
  async (_, { rejectWithValue }) => {
    try {
      const response = await notificationService.getUnreadNotifications();
      if (response.success) {
        return {
          notifications: response.data.notifications,
          unreadCount: response.data.unread_count,
        };
      } else {
        return rejectWithValue(response.message || 'Failed to fetch unread notifications');
      }
    } catch (error: any) {
      return rejectWithValue(error.message || 'Failed to fetch unread notifications');
    }
  }
);

export const markAsRead = createAsyncThunk(
  'notification/markAsRead',
  async (notificationId: number, { rejectWithValue }) => {
    try {
      const response = await notificationService.markAsRead(notificationId);
      if (response.success) {
        return notificationId;
      } else {
        return rejectWithValue(response.message || 'Failed to mark notification as read');
      }
    } catch (error: any) {
      return rejectWithValue(error.message || 'Failed to mark notification as read');
    }
  }
);

export const markAllAsRead = createAsyncThunk(
  'notification/markAllAsRead',
  async (_, { rejectWithValue }) => {
    try {
      const response = await notificationService.markAllAsRead();
      if (response.success) {
        return true;
      } else {
        return rejectWithValue(response.message || 'Failed to mark all notifications as read');
      }
    } catch (error: any) {
      return rejectWithValue(error.message || 'Failed to mark all notifications as read');
    }
  }
);

export const deleteNotification = createAsyncThunk(
  'notification/deleteNotification',
  async (notificationId: number, { rejectWithValue }) => {
    try {
      const response = await notificationService.deleteNotification(notificationId);
      if (response.success) {
        return notificationId;
      } else {
        return rejectWithValue(response.message || 'Failed to delete notification');
      }
    } catch (error: any) {
      return rejectWithValue(error.message || 'Failed to delete notification');
    }
  }
);

const notificationSlice = createSlice({
  name: 'notification',
  initialState,
  reducers: {
    clearError: (state) => {
      state.error = null;
    },
    addNotification: (state, action: PayloadAction<Notification>) => {
      state.notifications.unshift(action.payload);
      if (!action.payload.is_read) {
        state.unreadCount += 1;
      }
    },
    updateNotification: (state, action: PayloadAction<{ id: number; updates: Partial<Notification> }>) => {
      const index = state.notifications.findIndex(n => n.id === action.payload.id);
      if (index !== -1) {
        const notification = state.notifications[index];
        const wasUnread = !notification.is_read;
        state.notifications[index] = { ...notification, ...action.payload.updates };
        
        // Update unread count if read status changed
        if (wasUnread && state.notifications[index].is_read) {
          state.unreadCount = Math.max(0, state.unreadCount - 1);
        } else if (!wasUnread && !state.notifications[index].is_read) {
          state.unreadCount += 1;
        }
      }
    },
  },
  extraReducers: (builder) => {
    builder
      // Fetch notifications
      .addCase(fetchNotifications.pending, (state) => {
        state.isLoading = true;
        state.error = null;
      })
      .addCase(fetchNotifications.fulfilled, (state, action) => {
        state.isLoading = false;
        state.notifications = action.payload;
        state.unreadCount = action.payload.filter(n => !n.is_read).length;
        state.error = null;
      })
      .addCase(fetchNotifications.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
      })
      // Fetch unread notifications
      .addCase(fetchUnreadNotifications.pending, (state) => {
        state.isLoading = true;
        state.error = null;
      })
      .addCase(fetchUnreadNotifications.fulfilled, (state, action) => {
        state.isLoading = false;
        state.notifications = action.payload.notifications;
        state.unreadCount = action.payload.unreadCount;
        state.error = null;
      })
      .addCase(fetchUnreadNotifications.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
      })
      // Mark as read
      .addCase(markAsRead.pending, (state) => {
        state.isLoading = true;
        state.error = null;
      })
      .addCase(markAsRead.fulfilled, (state, action) => {
        state.isLoading = false;
        const index = state.notifications.findIndex(n => n.id === action.payload);
        if (index !== -1 && !state.notifications[index].is_read) {
          state.notifications[index].is_read = true;
          state.notifications[index].read_at = new Date().toISOString();
          state.unreadCount = Math.max(0, state.unreadCount - 1);
        }
        state.error = null;
      })
      .addCase(markAsRead.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
      })
      // Mark all as read
      .addCase(markAllAsRead.pending, (state) => {
        state.isLoading = true;
        state.error = null;
      })
      .addCase(markAllAsRead.fulfilled, (state) => {
        state.isLoading = false;
        state.notifications.forEach(notification => {
          if (!notification.is_read) {
            notification.is_read = true;
            notification.read_at = new Date().toISOString();
          }
        });
        state.unreadCount = 0;
        state.error = null;
      })
      .addCase(markAllAsRead.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
      })
      // Delete notification
      .addCase(deleteNotification.pending, (state) => {
        state.isLoading = true;
        state.error = null;
      })
      .addCase(deleteNotification.fulfilled, (state, action) => {
        state.isLoading = false;
        const index = state.notifications.findIndex(n => n.id === action.payload);
        if (index !== -1) {
          const wasUnread = !state.notifications[index].is_read;
          state.notifications.splice(index, 1);
          if (wasUnread) {
            state.unreadCount = Math.max(0, state.unreadCount - 1);
          }
        }
        state.error = null;
      })
      .addCase(deleteNotification.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
      });
  },
});

export const { clearError, addNotification, updateNotification } = notificationSlice.actions;
export default notificationSlice.reducer;