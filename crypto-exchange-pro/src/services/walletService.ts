import { apiService } from './api';
import { Wallet, Transaction, WalletAddress, ApiResponse } from '../types';

export const walletService = {
  async getWallets(): Promise<ApiResponse<{ wallets: Wallet[] }>> {
    return apiService.get('/wallets');
  },

  async getWallet(walletId: number): Promise<ApiResponse<{ wallet: Wallet }>> {
    return apiService.get(`/wallets/${walletId}`);
  },

  async generateDepositAddress(walletId: number, network?: string): Promise<ApiResponse<{ address: WalletAddress }>> {
    return apiService.post(`/wallets/${walletId}/deposit-address`, { network });
  },

  async createDeposit(data: {
    currency: string;
    amount: number;
    txid?: string;
    from_address?: string;
    memo?: string;
  }): Promise<ApiResponse<{ transaction: Transaction }>> {
    return apiService.post('/wallets/deposit', data);
  },

  async createWithdrawal(data: {
    currency: string;
    amount: number;
    fee: number;
    to_address: string;
    memo?: string;
  }): Promise<ApiResponse<{ transaction: Transaction }>> {
    return apiService.post('/wallets/withdraw', data);
  },

  async getTransactions(walletId: number, params?: {
    page?: number;
    per_page?: number;
    type?: string;
    status?: string;
  }): Promise<ApiResponse<{ transactions: Transaction[]; pagination: any }>> {
    return apiService.get(`/wallets/${walletId}/transactions`, { params });
  },

  async getAllTransactions(params?: {
    page?: number;
    per_page?: number;
    type?: string;
    status?: string;
    currency?: string;
  }): Promise<ApiResponse<{ transactions: Transaction[]; pagination: any }>> {
    return apiService.get('/wallets/transactions/all', { params });
  },
};