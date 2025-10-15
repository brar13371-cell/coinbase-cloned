import { apiService } from './api';
import { User, LoginForm, RegisterForm, ApiResponse } from '../types';

export const authService = {
  async login(credentials: LoginForm): Promise<ApiResponse<{ user: User; token: string }>> {
    return apiService.post('/auth/login', credentials);
  },

  async register(userData: RegisterForm): Promise<ApiResponse<{ user: User }>> {
    return apiService.post('/auth/register', userData);
  },

  async getCurrentUser(): Promise<ApiResponse<{ user: User }>> {
    return apiService.get('/auth/me');
  },

  async logout(): Promise<ApiResponse> {
    return apiService.post('/auth/logout');
  },

  async verifyEmail(data: { email: string; token: string }): Promise<ApiResponse> {
    return apiService.post('/auth/verify-email', data);
  },

  async enable2FA(): Promise<ApiResponse<{ secret: string; qr_code_url: string }>> {
    return apiService.post('/auth/2fa/enable');
  },

  async verify2FA(code: string): Promise<ApiResponse<{ backup_codes: string[] }>> {
    return apiService.post('/auth/2fa/verify', { code });
  },

  async disable2FA(): Promise<ApiResponse> {
    return apiService.post('/auth/2fa/disable');
  },

  async changePassword(data: { current_password: string; new_password: string; new_password_confirmation: string }): Promise<ApiResponse> {
    return apiService.post('/auth/change-password', data);
  },

  async forgotPassword(email: string): Promise<ApiResponse> {
    return apiService.post('/auth/forgot-password', { email });
  },

  async resetPassword(data: { email: string; token: string; password: string; password_confirmation: string }): Promise<ApiResponse> {
    return apiService.post('/auth/reset-password', data);
  },

  async getProfile(): Promise<ApiResponse<{ user: User; profile: any }>> {
    return apiService.get('/profile');
  },

  async updateProfile(data: Partial<User>): Promise<ApiResponse<{ user: User }>> {
    return apiService.put('/profile', data);
  },

  async uploadAvatar(file: File): Promise<ApiResponse<{ avatar_url: string }>> {
    const formData = new FormData();
    formData.append('avatar', file);
    return apiService.upload('/profile/avatar', formData);
  },

  async deleteAvatar(): Promise<ApiResponse> {
    return apiService.delete('/profile/avatar');
  },

  async getSessions(): Promise<ApiResponse<{ sessions: any[] }>> {
    return apiService.get('/security/sessions');
  },

  async revokeSession(sessionId: string): Promise<ApiResponse> {
    return apiService.delete(`/security/sessions/${sessionId}`);
  },

  async revokeAllSessions(): Promise<ApiResponse> {
    return apiService.post('/security/sessions/revoke-all');
  },

  async getAuditLogs(): Promise<ApiResponse<{ logs: any[] }>> {
    return apiService.get('/security/audit-logs');
  },
};