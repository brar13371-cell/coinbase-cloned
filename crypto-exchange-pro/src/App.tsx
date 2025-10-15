import React from 'react';
import { Routes, Route, Navigate } from 'react-router-dom';
import { useSelector } from 'react-redux';
import { RootState } from './store';

// Pages
import LandingPage from './pages/LandingPage';
import LoginPage from './pages/LoginPage';
import RegisterPage from './pages/RegisterPage';
import DashboardPage from './pages/DashboardPage';
import TradingPage from './pages/TradingPage';
import PortfolioPage from './pages/PortfolioPage';
import WalletPage from './pages/WalletPage';
import KycPage from './pages/KycPage';
import SettingsPage from './pages/SettingsPage';
import AdminDashboard from './pages/admin/AdminDashboard';
import AdminUsers from './pages/admin/AdminUsers';
import AdminKyc from './pages/admin/AdminKyc';
import AdminTransactions from './pages/admin/AdminTransactions';
import AdminTrading from './pages/admin/AdminTrading';
import AdminSystem from './pages/admin/AdminSystem';

// Components
import Layout from './components/Layout';
import ProtectedRoute from './components/ProtectedRoute';
import AdminRoute from './components/AdminRoute';
import LoadingSpinner from './components/LoadingSpinner';

const App: React.FC = () => {
  const { isAuthenticated, isLoading, user } = useSelector((state: RootState) => state.auth);

  if (isLoading) {
    return <LoadingSpinner />;
  }

  return (
    <div className="min-h-screen bg-gray-50">
      <Routes>
        {/* Public routes */}
        <Route path="/" element={<LandingPage />} />
        <Route path="/login" element={<LoginPage />} />
        <Route path="/register" element={<RegisterPage />} />

        {/* Protected routes */}
        <Route
          path="/dashboard"
          element={
            <ProtectedRoute>
              <Layout>
                <DashboardPage />
              </Layout>
            </ProtectedRoute>
          }
        />
        <Route
          path="/trading"
          element={
            <ProtectedRoute>
              <Layout>
                <TradingPage />
              </Layout>
            </ProtectedRoute>
          }
        />
        <Route
          path="/trading/:pair"
          element={
            <ProtectedRoute>
              <Layout>
                <TradingPage />
              </Layout>
            </ProtectedRoute>
          }
        />
        <Route
          path="/portfolio"
          element={
            <ProtectedRoute>
              <Layout>
                <PortfolioPage />
              </Layout>
            </ProtectedRoute>
          }
        />
        <Route
          path="/wallet"
          element={
            <ProtectedRoute>
              <Layout>
                <WalletPage />
              </Layout>
            </ProtectedRoute>
          }
        />
        <Route
          path="/kyc"
          element={
            <ProtectedRoute>
              <Layout>
                <KycPage />
              </Layout>
            </ProtectedRoute>
          }
        />
        <Route
          path="/settings"
          element={
            <ProtectedRoute>
              <Layout>
                <SettingsPage />
              </Layout>
            </ProtectedRoute>
          }
        />

        {/* Admin routes */}
        <Route
          path="/admin"
          element={
            <AdminRoute>
              <Layout>
                <AdminDashboard />
              </Layout>
            </AdminRoute>
          }
        />
        <Route
          path="/admin/users"
          element={
            <AdminRoute>
              <Layout>
                <AdminUsers />
              </Layout>
            </AdminRoute>
          }
        />
        <Route
          path="/admin/kyc"
          element={
            <AdminRoute>
              <Layout>
                <AdminKyc />
              </Layout>
            </AdminRoute>
          }
        />
        <Route
          path="/admin/transactions"
          element={
            <AdminRoute>
              <Layout>
                <AdminTransactions />
              </Layout>
            </AdminRoute>
          }
        />
        <Route
          path="/admin/trading"
          element={
            <AdminRoute>
              <Layout>
                <AdminTrading />
              </Layout>
            </AdminRoute>
          }
        />
        <Route
          path="/admin/system"
          element={
            <AdminRoute>
              <Layout>
                <AdminSystem />
              </Layout>
            </AdminRoute>
          }
        />

        {/* Catch all route */}
        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    </div>
  );
};

export default App;