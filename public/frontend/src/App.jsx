import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { Suspense, lazy } from 'react';
import './App.css';
import { AuthProvider, useAuth } from './contexts/AuthContext';
import { Toaster } from 'react-hot-toast';

// Lazy load pages for better performance
const Login = lazy(() => import('./pages/Login'));
const Register = lazy(() => import('./pages/Register'));
const Dashboard = lazy(() => import('./pages/Dashboard'));
const Transactions = lazy(() => import('./pages/Transactions'));
const Accounts = lazy(() => import('./pages/Accounts'));
const Categories = lazy(() => import('./pages/Categories'));
const Debts = lazy(() => import('./pages/Debts'));
const Statistics = lazy(() => import('./pages/Statistics'));
const Settings = lazy(() => import('./pages/Settings'));
const NotFound = lazy(() => import('./pages/NotFound'));

// Import Layout component
import Layout from './components/layout/Layout';

// Protected route component
const ProtectedRoute = ({ children }) => {
  const { isAuthenticated, loading } = useAuth();
  
  if (loading) {
    return <div className="flex h-screen items-center justify-center bg-background">
      <div className="h-12 w-12 loading-spinner border-blue-500"></div>
    </div>;
  }

  if (!isAuthenticated) {
    return <Navigate to="/login" replace />;
  }

  return children;
};

function App() {
  return (
    <AuthProvider>
      <Router>
        <Suspense fallback={
          <div className="flex h-screen items-center justify-center bg-background">
            <div className="h-12 w-12 loading-spinner border-blue-500"></div>
          </div>
        }>
          <Routes>
            {/* Public routes */}
            <Route path="/login" element={<Login />} />
            <Route path="/register" element={<Register />} />
            
            {/* Protected routes */}
            <Route path="/dashboard" element={
              <ProtectedRoute><Layout><Dashboard /></Layout></ProtectedRoute>
            } />
            <Route path="/transactions" element={
              <ProtectedRoute><Layout><Transactions /></Layout></ProtectedRoute>
            } />
            <Route path="/accounts" element={
              <ProtectedRoute><Layout><Accounts /></Layout></ProtectedRoute>
            } />
            <Route path="/categories" element={
              <ProtectedRoute><Layout><Categories /></Layout></ProtectedRoute>
            } />
            <Route path="/debts" element={
              <ProtectedRoute><Layout><Debts /></Layout></ProtectedRoute>
            } />
            <Route path="/statistics" element={
              <ProtectedRoute><Layout><Statistics /></Layout></ProtectedRoute>
            } />
            <Route path="/settings" element={
              <ProtectedRoute><Layout><Settings /></Layout></ProtectedRoute>
            } />
            
            {/* Default routes */}
            <Route path="/" element={<Navigate to="/dashboard" replace />} />
            <Route path="*" element={<NotFound />} />
          </Routes>
        </Suspense>
        <Toaster position="top-right" toastOptions={{
          duration: 3000,
          style: {
            background: '#333',
            color: '#fff',
          },
        }} />
      </Router>
    </AuthProvider>
  )
}

export default App
