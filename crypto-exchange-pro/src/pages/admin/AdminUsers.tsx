import React from 'react';
import { UsersIcon } from '@heroicons/react/24/outline';

const AdminUsers: React.FC = () => {
  return (
    <div className="space-y-6">
      <div className="bg-white rounded-lg shadow p-6">
        <div className="flex items-center">
          <UsersIcon className="h-8 w-8 text-primary-600 mr-3" />
          <h1 className="text-2xl font-bold text-gray-900">User Management</h1>
        </div>
        <p className="text-gray-600 mt-2">
          User management interface coming soon...
        </p>
      </div>
    </div>
  );
};

export default AdminUsers;