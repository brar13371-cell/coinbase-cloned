import React from 'react';
import { ChartBarIcon } from '@heroicons/react/24/outline';

const AdminTrading: React.FC = () => {
  return (
    <div className="space-y-6">
      <div className="bg-white rounded-lg shadow p-6">
        <div className="flex items-center">
          <ChartBarIcon className="h-8 w-8 text-primary-600 mr-3" />
          <h1 className="text-2xl font-bold text-gray-900">Trading Management</h1>
        </div>
        <p className="text-gray-600 mt-2">
          Trading management interface coming soon...
        </p>
      </div>
    </div>
  );
};

export default AdminTrading;