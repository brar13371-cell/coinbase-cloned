import { configureStore } from '@reduxjs/toolkit';
import authSlice from './slices/authSlice';
import walletSlice from './slices/walletSlice';
import tradingSlice from './slices/tradingSlice';
import notificationSlice from './slices/notificationSlice';

export const store = configureStore({
  reducer: {
    auth: authSlice,
    wallet: walletSlice,
    trading: tradingSlice,
    notification: notificationSlice,
  },
  middleware: (getDefaultMiddleware) =>
    getDefaultMiddleware({
      serializableCheck: {
        ignoredActions: ['persist/PERSIST'],
      },
    }),
});

export type RootState = ReturnType<typeof store.getState>;
export type AppDispatch = typeof store.dispatch;