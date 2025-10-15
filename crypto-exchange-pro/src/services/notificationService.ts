import { apiService } from './api';
import { Notification, ApiResponse } from '../types';

export const notificationService = {
  async getNotifications(params?: {
    page?: number;
    per_page?: number;
    type?: string;
  }): Promise<ApiResponse<{ notifications: Notification[]; pagination: any }>> {
    return apiService.get('/notifications', { params });
  },

  async getUnreadNotifications(): Promise<ApiResponse<{ notifications: Notification[]; unread_count: number }>> {
    return apiService.get('/notifications/unread');
  },

  async markAsRead(notificationId: number): Promise<ApiResponse> {
    return apiService.post(`/notifications/${notificationId}/read`);
  },

  async markAllAsRead(): Promise<ApiResponse> {
    return apiService.post('/notifications/read-all');
  },

  async deleteNotification(notificationId: number): Promise<ApiResponse> {
    return apiService.delete(`/notifications/${notificationId}`);
  },
};