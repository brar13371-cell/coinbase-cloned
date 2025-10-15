import React from 'react';
import { CurrencyDollarIcon } from '@heroicons/react/24/outline';

const AdminTransactions: React.FC = () => {
  return (
    <div className="space-y-6">
      <div className="bg-white rounded-lg shadow p-6">
        <div className="flex items-center">
          <CurrencyDollarIcon className="h-8 w-8 text-primary-600 mr-3" />
          <h1 className="text-2xl font-bold text-gray-900">Transaction Management</h1>
        </div>
        <p className="text-gray-600 mt-2">
          Transaction management interface coming soon...
        </p>
      </div>
    </div>
  );
};

export default AdminTransactions;