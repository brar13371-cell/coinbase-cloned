import React from 'react';
import { WalletIcon } from '@heroicons/react/24/outline';

const PortfolioPage: React.FC = () => {
  return (
    <div className="space-y-6">
      <div className="bg-white rounded-lg shadow p-6">
        <div className="flex items-center">
          <WalletIcon className="h-8 w-8 text-primary-600 mr-3" />
          <h1 className="text-2xl font-bold text-gray-900">Portfolio</h1>
        </div>
        <p className="text-gray-600 mt-2">
          Portfolio management interface coming soon...
        </p>
      </div>
    </div>
  );
};

export default PortfolioPage;