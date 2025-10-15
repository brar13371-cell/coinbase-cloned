import { createSlice, createAsyncThunk, PayloadAction } from '@reduxjs/toolkit';
import { TradingPair, Order, Trade, OrderBookEntry, MarketData, PlaceOrderForm, ApiResponse } from '../../types';
import { tradingService } from '../../services/tradingService';

interface TradingState {
  pairs: TradingPair[];
  selectedPair: TradingPair | null;
  orders: Order[];
  trades: Trade[];
  orderBook: {
    bids: OrderBookEntry[];
    asks: OrderBookEntry[];
  };
  marketData: MarketData | null;
  isLoading: boolean;
  error: string | null;
}

const initialState: TradingState = {
  pairs: [],
  selectedPair: null,
  orders: [],
  trades: [],
  orderBook: {
    bids: [],
    asks: [],
  },
  marketData: null,
  isLoading: false,
  error: null,
};

// Async thunks
export const fetchPairs = createAsyncThunk(
  'trading/fetchPairs',
  async (_, { rejectWithValue }) => {
    try {
      const response = await tradingService.getPairs();
      if (response.success) {
        return response.data.pairs;
      } else {
        return rejectWithValue(response.message || 'Failed to fetch trading pairs');
      }
    } catch (error: any) {
      return rejectWithValue(error.message || 'Failed to fetch trading pairs');
    }
  }
);

export const fetchMarketData = createAsyncThunk(
  'trading/fetchMarketData',
  async (pairId: number, { rejectWithValue }) => {
    try {
      const response = await tradingService.getMarketData(pairId);
      if (response.success) {
        return response.data;
      } else {
        return rejectWithValue(response.message || 'Failed to fetch market data');
      }
    } catch (error: any) {
      return rejectWithValue(error.message || 'Failed to fetch market data');
    }
  }
);

export const fetchOrderBook = createAsyncThunk(
  'trading/fetchOrderBook',
  async (pairId: number, { rejectWithValue }) => {
    try {
      const response = await tradingService.getOrderBook(pairId);
      if (response.success) {
        return response.data.order_book;
      } else {
        return rejectWithValue(response.message || 'Failed to fetch order book');
      }
    } catch (error: any) {
      return rejectWithValue(error.message || 'Failed to fetch order book');
    }
  }
);

export const fetchRecentTrades = createAsyncThunk(
  'trading/fetchRecentTrades',
  async (pairId: number, { rejectWithValue }) => {
    try {
      const response = await tradingService.getRecentTrades(pairId);
      if (response.success) {
        return response.data.trades;
      } else {
        return rejectWithValue(response.message || 'Failed to fetch recent trades');
      }
    } catch (error: any) {
      return rejectWithValue(error.message || 'Failed to fetch recent trades');
    }
  }
);

export const fetchOrders = createAsyncThunk(
  'trading/fetchOrders',
  async (params: { pairId?: number; status?: string; side?: string; type?: string } = {}, { rejectWithValue }) => {
    try {
      const response = await tradingService.getOrders(params);
      if (response.success) {
        return response.data.orders;
      } else {
        return rejectWithValue(response.message || 'Failed to fetch orders');
      }
    } catch (error: any) {
      return rejectWithValue(error.message || 'Failed to fetch orders');
    }
  }
);

export const placeOrder = createAsyncThunk(
  'trading/placeOrder',
  async (orderData: PlaceOrderForm, { rejectWithValue }) => {
    try {
      const response = await tradingService.placeOrder(orderData);
      if (response.success) {
        return response.data.order;
      } else {
        return rejectWithValue(response.message || 'Failed to place order');
      }
    } catch (error: any) {
      return rejectWithValue(error.message || 'Failed to place order');
    }
  }
);

export const cancelOrder = createAsyncThunk(
  'trading/cancelOrder',
  async (orderId: number, { rejectWithValue }) => {
    try {
      const response = await tradingService.cancelOrder(orderId);
      if (response.success) {
        return orderId;
      } else {
        return rejectWithValue(response.message || 'Failed to cancel order');
      }
    } catch (error: any) {
      return rejectWithValue(error.message || 'Failed to cancel order');
    }
  }
);

export const fetchUserTrades = createAsyncThunk(
  'trading/fetchUserTrades',
  async (params: { pairId?: number } = {}, { rejectWithValue }) => {
    try {
      const response = await tradingService.getUserTrades(params);
      if (response.success) {
        return response.data.trades;
      } else {
        return rejectWithValue(response.message || 'Failed to fetch user trades');
      }
    } catch (error: any) {
      return rejectWithValue(error.message || 'Failed to fetch user trades');
    }
  }
);

const tradingSlice = createSlice({
  name: 'trading',
  initialState,
  reducers: {
    clearError: (state) => {
      state.error = null;
    },
    setSelectedPair: (state, action: PayloadAction<TradingPair | null>) => {
      state.selectedPair = action.payload;
    },
    updateOrderBook: (state, action: PayloadAction<{ bids: OrderBookEntry[]; asks: OrderBookEntry[] }>) => {
      state.orderBook = action.payload;
    },
    updateMarketData: (state, action: PayloadAction<MarketData>) => {
      state.marketData = action.payload;
    },
    addTrade: (state, action: PayloadAction<Trade>) => {
      state.trades.unshift(action.payload);
    },
    updateOrder: (state, action: PayloadAction<{ id: number; updates: Partial<Order> }>) => {
      const index = state.orders.findIndex(o => o.id === action.payload.id);
      if (index !== -1) {
        state.orders[index] = { ...state.orders[index], ...action.payload.updates };
      }
    },
  },
  extraReducers: (builder) => {
    builder
      // Fetch pairs
      .addCase(fetchPairs.pending, (state) => {
        state.isLoading = true;
        state.error = null;
      })
      .addCase(fetchPairs.fulfilled, (state, action) => {
        state.isLoading = false;
        state.pairs = action.payload;
        state.error = null;
      })
      .addCase(fetchPairs.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
      })
      // Fetch market data
      .addCase(fetchMarketData.pending, (state) => {
        state.isLoading = true;
        state.error = null;
      })
      .addCase(fetchMarketData.fulfilled, (state, action) => {
        state.isLoading = false;
        state.marketData = action.payload.market_data;
        state.error = null;
      })
      .addCase(fetchMarketData.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
      })
      // Fetch order book
      .addCase(fetchOrderBook.pending, (state) => {
        state.isLoading = true;
        state.error = null;
      })
      .addCase(fetchOrderBook.fulfilled, (state, action) => {
        state.isLoading = false;
        state.orderBook = action.payload;
        state.error = null;
      })
      .addCase(fetchOrderBook.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
      })
      // Fetch recent trades
      .addCase(fetchRecentTrades.pending, (state) => {
        state.isLoading = true;
        state.error = null;
      })
      .addCase(fetchRecentTrades.fulfilled, (state, action) => {
        state.isLoading = false;
        state.trades = action.payload;
        state.error = null;
      })
      .addCase(fetchRecentTrades.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
      })
      // Fetch orders
      .addCase(fetchOrders.pending, (state) => {
        state.isLoading = true;
        state.error = null;
      })
      .addCase(fetchOrders.fulfilled, (state, action) => {
        state.isLoading = false;
        state.orders = action.payload;
        state.error = null;
      })
      .addCase(fetchOrders.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
      })
      // Place order
      .addCase(placeOrder.pending, (state) => {
        state.isLoading = true;
        state.error = null;
      })
      .addCase(placeOrder.fulfilled, (state, action) => {
        state.isLoading = false;
        state.orders.unshift(action.payload);
        state.error = null;
      })
      .addCase(placeOrder.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
      })
      // Cancel order
      .addCase(cancelOrder.pending, (state) => {
        state.isLoading = true;
        state.error = null;
      })
      .addCase(cancelOrder.fulfilled, (state, action) => {
        state.isLoading = false;
        const index = state.orders.findIndex(o => o.id === action.payload);
        if (index !== -1) {
          state.orders[index].status = 'cancelled';
          state.orders[index].cancelled_at = new Date().toISOString();
        }
        state.error = null;
      })
      .addCase(cancelOrder.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
      })
      // Fetch user trades
      .addCase(fetchUserTrades.pending, (state) => {
        state.isLoading = true;
        state.error = null;
      })
      .addCase(fetchUserTrades.fulfilled, (state, action) => {
        state.isLoading = false;
        state.trades = action.payload;
        state.error = null;
      })
      .addCase(fetchUserTrades.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
      });
  },
});

export const { 
  clearError, 
  setSelectedPair, 
  updateOrderBook, 
  updateMarketData, 
  addTrade, 
  updateOrder 
} = tradingSlice.actions;
export default tradingSlice.reducer;