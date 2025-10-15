import React from 'react';
import { Cog6ToothIcon } from '@heroicons/react/24/outline';

const AdminSystem: React.FC = () => {
  return (
    <div className="space-y-6">
      <div className="bg-white rounded-lg shadow p-6">
        <div className="flex items-center">
          <Cog6ToothIcon className="h-8 w-8 text-primary-600 mr-3" />
          <h1 className="text-2xl font-bold text-gray-900">System Management</h1>
        </div>
        <p className="text-gray-600 mt-2">
          System management interface coming soon...
        </p>
      </div>
    </div>
  );
};

export default AdminSystem;