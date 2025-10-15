import { createSlice, createAsyncThunk, PayloadAction } from '@reduxjs/toolkit';
import { Wallet, Transaction, ApiResponse } from '../../types';
import { walletService } from '../../services/walletService';

interface WalletState {
  wallets: Wallet[];
  transactions: Transaction[];
  selectedWallet: Wallet | null;
  isLoading: boolean;
  error: string | null;
}

const initialState: WalletState = {
  wallets: [],
  transactions: [],
  selectedWallet: null,
  isLoading: false,
  error: null,
};

// Async thunks
export const fetchWallets = createAsyncThunk(
  'wallet/fetchWallets',
  async (_, { rejectWithValue }) => {
    try {
      const response = await walletService.getWallets();
      if (response.success) {
        return response.data.wallets;
      } else {
        return rejectWithValue(response.message || 'Failed to fetch wallets');
      }
    } catch (error: any) {
      return rejectWithValue(error.message || 'Failed to fetch wallets');
    }
  }
);

export const fetchTransactions = createAsyncThunk(
  'wallet/fetchTransactions',
  async (walletId: number, { rejectWithValue }) => {
    try {
      const response = await walletService.getTransactions(walletId);
      if (response.success) {
        return response.data.transactions;
      } else {
        return rejectWithValue(response.message || 'Failed to fetch transactions');
      }
    } catch (error: any) {
      return rejectWithValue(error.message || 'Failed to fetch transactions');
    }
  }
);

export const generateDepositAddress = createAsyncThunk(
  'wallet/generateDepositAddress',
  async (data: { walletId: number; network?: string }, { rejectWithValue }) => {
    try {
      const response = await walletService.generateDepositAddress(data.walletId, data.network);
      if (response.success) {
        return response.data.address;
      } else {
        return rejectWithValue(response.message || 'Failed to generate deposit address');
      }
    } catch (error: any) {
      return rejectWithValue(error.message || 'Failed to generate deposit address');
    }
  }
);

export const createWithdrawal = createAsyncThunk(
  'wallet/createWithdrawal',
  async (data: {
    currency: string;
    amount: number;
    fee: number;
    to_address: string;
    memo?: string;
  }, { rejectWithValue }) => {
    try {
      const response = await walletService.createWithdrawal(data);
      if (response.success) {
        return response.data.transaction;
      } else {
        return rejectWithValue(response.message || 'Failed to create withdrawal');
      }
    } catch (error: any) {
      return rejectWithValue(error.message || 'Failed to create withdrawal');
    }
  }
);

const walletSlice = createSlice({
  name: 'wallet',
  initialState,
  reducers: {
    clearError: (state) => {
      state.error = null;
    },
    setSelectedWallet: (state, action: PayloadAction<Wallet | null>) => {
      state.selectedWallet = action.payload;
    },
    updateWalletBalance: (state, action: PayloadAction<{ walletId: number; balance: number }>) => {
      const wallet = state.wallets.find(w => w.id === action.payload.walletId);
      if (wallet) {
        wallet.balance_available = action.payload.balance;
        wallet.balance_total = action.payload.balance + wallet.balance_reserved;
        wallet.formatted_balance = action.payload.balance.toFixed(8);
      }
    },
    addTransaction: (state, action: PayloadAction<Transaction>) => {
      state.transactions.unshift(action.payload);
    },
    updateTransaction: (state, action: PayloadAction<{ id: number; updates: Partial<Transaction> }>) => {
      const index = state.transactions.findIndex(t => t.id === action.payload.id);
      if (index !== -1) {
        state.transactions[index] = { ...state.transactions[index], ...action.payload.updates };
      }
    },
  },
  extraReducers: (builder) => {
    builder
      // Fetch wallets
      .addCase(fetchWallets.pending, (state) => {
        state.isLoading = true;
        state.error = null;
      })
      .addCase(fetchWallets.fulfilled, (state, action) => {
        state.isLoading = false;
        state.wallets = action.payload;
        state.error = null;
      })
      .addCase(fetchWallets.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
      })
      // Fetch transactions
      .addCase(fetchTransactions.pending, (state) => {
        state.isLoading = true;
        state.error = null;
      })
      .addCase(fetchTransactions.fulfilled, (state, action) => {
        state.isLoading = false;
        state.transactions = action.payload;
        state.error = null;
      })
      .addCase(fetchTransactions.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
      })
      // Generate deposit address
      .addCase(generateDepositAddress.pending, (state) => {
        state.isLoading = true;
        state.error = null;
      })
      .addCase(generateDepositAddress.fulfilled, (state, action) => {
        state.isLoading = false;
        // Update wallet with new address
        const wallet = state.wallets.find(w => w.id === action.payload.id);
        if (wallet) {
          wallet.address = action.payload.address;
          wallet.network = action.payload.network;
        }
        state.error = null;
      })
      .addCase(generateDepositAddress.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
      })
      // Create withdrawal
      .addCase(createWithdrawal.pending, (state) => {
        state.isLoading = true;
        state.error = null;
      })
      .addCase(createWithdrawal.fulfilled, (state, action) => {
        state.isLoading = false;
        state.transactions.unshift(action.payload);
        state.error = null;
      })
      .addCase(createWithdrawal.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
      });
  },
});

export const { 
  clearError, 
  setSelectedWallet, 
  updateWalletBalance, 
  addTransaction, 
  updateTransaction 
} = walletSlice.actions;
export default walletSlice.reducer;