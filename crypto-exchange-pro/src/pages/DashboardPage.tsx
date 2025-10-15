import React, { useEffect } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { RootState } from '../store';
import { fetchWallets } from '../store/slices/walletSlice';
import { fetchPairs } from '../store/slices/tradingSlice';
import { 
  ChartBarIcon, 
  CurrencyDollarIcon, 
  ArrowUpIcon, 
  ArrowDownIcon,
  WalletIcon,
  TrendingUpIcon,
  TrendingDownIcon,
} from '@heroicons/react/24/outline';

const DashboardPage: React.FC = () => {
  const dispatch = useDispatch();
  const { user } = useSelector((state: RootState) => state.auth);
  const { wallets, isLoading: walletsLoading } = useSelector((state: RootState) => state.wallet);
  const { pairs, isLoading: pairsLoading } = useSelector((state: RootState) => state.trading);

  useEffect(() => {
    dispatch(fetchWallets());
    dispatch(fetchPairs());
  }, [dispatch]);

  const totalBalance = wallets.reduce((sum, wallet) => sum + wallet.balance_total, 0);
  const topPairs = pairs.slice(0, 5);

  const formatCurrency = (amount: number, currency: string = 'USD') => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: currency,
    }).format(amount);
  };

  const formatPercentage = (value: number) => {
    return `${value >= 0 ? '+' : ''}${value.toFixed(2)}%`;
  };

  return (
    <div className="space-y-6">
      {/* Welcome Header */}
      <div className="bg-white rounded-lg shadow p-6">
        <h1 className="text-2xl font-bold text-gray-900">
          Welcome back, {user?.profile?.first_name || user?.email}!
        </h1>
        <p className="text-gray-600 mt-1">
          Here's what's happening with your portfolio today.
        </p>
      </div>

      {/* Portfolio Overview */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div className="bg-white rounded-lg shadow p-6">
          <div className="flex items-center">
            <div className="flex-shrink-0">
              <WalletIcon className="h-8 w-8 text-primary-600" />
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-500">Total Balance</p>
              <p className="text-2xl font-semibold text-gray-900">
                {formatCurrency(totalBalance)}
              </p>
            </div>
          </div>
        </div>

        <div className="bg-white rounded-lg shadow p-6">
          <div className="flex items-center">
            <div className="flex-shrink-0">
              <TrendingUpIcon className="h-8 w-8 text-green-600" />
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-500">24h Change</p>
              <p className="text-2xl font-semibold text-green-600">
                +$1,234.56
              </p>
            </div>
          </div>
        </div>

        <div className="bg-white rounded-lg shadow p-6">
          <div className="flex items-center">
            <div className="flex-shrink-0">
              <ChartBarIcon className="h-8 w-8 text-blue-600" />
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-500">Active Orders</p>
              <p className="text-2xl font-semibold text-gray-900">3</p>
            </div>
          </div>
        </div>

        <div className="bg-white rounded-lg shadow p-6">
          <div className="flex items-center">
            <div className="flex-shrink-0">
              <CurrencyDollarIcon className="h-8 w-8 text-purple-600" />
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-500">24h Volume</p>
              <p className="text-2xl font-semibold text-gray-900">
                {formatCurrency(12345.67)}
              </p>
            </div>
          </div>
        </div>
      </div>

      {/* Wallets and Market Data */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Wallets */}
        <div className="bg-white rounded-lg shadow">
          <div className="px-6 py-4 border-b border-gray-200">
            <h3 className="text-lg font-medium text-gray-900">Your Wallets</h3>
          </div>
          <div className="p-6">
            {walletsLoading ? (
              <div className="flex justify-center py-8">
                <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
              </div>
            ) : wallets.length > 0 ? (
              <div className="space-y-4">
                {wallets.slice(0, 5).map((wallet) => (
                  <div key={wallet.id} className="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div className="flex items-center">
                      <div className="h-10 w-10 bg-primary-100 rounded-full flex items-center justify-center">
                        <span className="text-primary-600 font-semibold text-sm">
                          {wallet.currency}
                        </span>
                      </div>
                      <div className="ml-3">
                        <p className="text-sm font-medium text-gray-900">{wallet.currency}</p>
                        <p className="text-xs text-gray-500">{wallet.wallet_type}</p>
                      </div>
                    </div>
                    <div className="text-right">
                      <p className="text-sm font-medium text-gray-900">
                        {formatCurrency(wallet.balance_total, wallet.currency)}
                      </p>
                      <p className="text-xs text-gray-500">
                        Available: {formatCurrency(wallet.balance_available, wallet.currency)}
                      </p>
                    </div>
                  </div>
                ))}
              </div>
            ) : (
              <div className="text-center py-8">
                <WalletIcon className="mx-auto h-12 w-12 text-gray-400" />
                <h3 className="mt-2 text-sm font-medium text-gray-900">No wallets</h3>
                <p className="mt-1 text-sm text-gray-500">Get started by creating a wallet.</p>
              </div>
            )}
          </div>
        </div>

        {/* Market Overview */}
        <div className="bg-white rounded-lg shadow">
          <div className="px-6 py-4 border-b border-gray-200">
            <h3 className="text-lg font-medium text-gray-900">Market Overview</h3>
          </div>
          <div className="p-6">
            {pairsLoading ? (
              <div className="flex justify-center py-8">
                <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
              </div>
            ) : topPairs.length > 0 ? (
              <div className="space-y-4">
                {topPairs.map((pair) => (
                  <div key={pair.id} className="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div className="flex items-center">
                      <div className="h-10 w-10 bg-primary-100 rounded-full flex items-center justify-center">
                        <span className="text-primary-600 font-semibold text-sm">
                          {pair.base_currency}
                        </span>
                      </div>
                      <div className="ml-3">
                        <p className="text-sm font-medium text-gray-900">{pair.symbol}</p>
                        <p className="text-xs text-gray-500">
                          {pair.base_currency}/{pair.quote_currency}
                        </p>
                      </div>
                    </div>
                    <div className="text-right">
                      <p className="text-sm font-medium text-gray-900">
                        {formatCurrency(pair.price, pair.quote_currency)}
                      </p>
                      <div className="flex items-center">
                        {pair.change_24h >= 0 ? (
                          <ArrowUpIcon className="h-4 w-4 text-green-500" />
                        ) : (
                          <ArrowDownIcon className="h-4 w-4 text-red-500" />
                        )}
                        <span
                          className={`text-xs font-medium ml-1 ${
                            pair.change_24h >= 0 ? 'text-green-600' : 'text-red-600'
                          }`}
                        >
                          {formatPercentage(pair.change_24h)}
                        </span>
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            ) : (
              <div className="text-center py-8">
                <ChartBarIcon className="mx-auto h-12 w-12 text-gray-400" />
                <h3 className="mt-2 text-sm font-medium text-gray-900">No market data</h3>
                <p className="mt-1 text-sm text-gray-500">Market data will appear here.</p>
              </div>
            )}
          </div>
        </div>
      </div>

      {/* Quick Actions */}
      <div className="bg-white rounded-lg shadow p-6">
        <h3 className="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <button className="flex items-center justify-center px-4 py-3 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
            <ChartBarIcon className="h-5 w-5 mr-2" />
            Start Trading
          </button>
          <button className="flex items-center justify-center px-4 py-3 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
            <WalletIcon className="h-5 w-5 mr-2" />
            Manage Wallets
          </button>
          <button className="flex items-center justify-center px-4 py-3 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
            <CurrencyDollarIcon className="h-5 w-5 mr-2" />
            Deposit Funds
          </button>
        </div>
      </div>
    </div>
  );
};

export default DashboardPage;