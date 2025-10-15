import { apiService } from './api';
import { TradingPair, Order, Trade, OrderBookEntry, MarketData, PlaceOrderForm, ApiResponse } from '../types';

export const tradingService = {
  async getPairs(): Promise<ApiResponse<{ pairs: TradingPair[] }>> {
    return apiService.get('/trading/pairs');
  },

  async getMarketData(pairId: number): Promise<ApiResponse<{ pair: TradingPair; market_data: MarketData }>> {
    return apiService.get(`/trading/pairs/${pairId}/data`);
  },

  async getOrderBook(pairId: number, limit?: number): Promise<ApiResponse<{ pair: TradingPair; order_book: { bids: OrderBookEntry[]; asks: OrderBookEntry[] } }>> {
    return apiService.get(`/trading/pairs/${pairId}/orderbook`, { params: { limit } });
  },

  async getRecentTrades(pairId: number, limit?: number): Promise<ApiResponse<{ pair: TradingPair; trades: Trade[] }>> {
    return apiService.get(`/trading/pairs/${pairId}/trades`, { params: { limit } });
  },

  async placeOrder(orderData: PlaceOrderForm): Promise<ApiResponse<{ order: Order }>> {
    return apiService.post('/trading/orders', orderData);
  },

  async getOrders(params?: {
    pair_id?: number;
    status?: string;
    side?: string;
    type?: string;
    page?: number;
    per_page?: number;
  }): Promise<ApiResponse<{ orders: Order[]; pagination: any }>> {
    return apiService.get('/trading/orders', { params });
  },

  async cancelOrder(orderId: number): Promise<ApiResponse> {
    return apiService.delete(`/trading/orders/${orderId}`);
  },

  async getUserTrades(params?: {
    pair_id?: number;
    page?: number;
    per_page?: number;
  }): Promise<ApiResponse<{ trades: Trade[]; pagination: any }>> {
    return apiService.get('/trading/trades', { params });
  },
};