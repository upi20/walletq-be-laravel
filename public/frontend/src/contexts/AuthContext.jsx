import { createContext, useContext, useState, useEffect } from 'react';
import { authService } from '../services/api';

const AuthContext = createContext();

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  // Check if user is logged in on mount
  useEffect(() => {
    checkAuth();
  }, []);

  // Check authentication status
  const checkAuth = async () => {
    const token = localStorage.getItem('token');
    if (!token) {
      setLoading(false);
      return;
    }

    try {
      const response = await authService.getProfile();
      setUser(response.data);
    } catch (err) {
      console.error("Auth check failed", err);
      setError(err.response?.data?.message || "Gagal memeriksa otentikasi");
      localStorage.removeItem('token');
    } finally {
      setLoading(false);
    }
  };

  // Login user
  const login = async (email, password) => {
    setLoading(true);
    setError(null);
    
    try {
      const response = await authService.login(email, password);
      const { token, user } = response.data.data;
      
      localStorage.setItem('token', token);
      setUser(user);
      
      return { success: true };
    } catch (err) {
      const errorMessage = err.response?.data?.message || "Login gagal";
      setError(errorMessage);
      return { success: false, message: errorMessage };
    } finally {
      setLoading(false);
    }
  };

  // Register user
  const register = async (name, email, password, password_confirmation) => {
    setLoading(true);
    setError(null);
    
    try {
      const response = await authService.register(name, email, password, password_confirmation);
      const { token, user } = response.data.data;
      
      localStorage.setItem('token', token);
      setUser(user);
      
      return { success: true };
    } catch (err) {
      const errorMessage = err.response?.data?.message || "Registrasi gagal";
      const errors = err.response?.data?.errors || {};
      setError(errorMessage);
      return { success: false, message: errorMessage, errors };
    } finally {
      setLoading(false);
    }
  };

  // Logout user
  const logout = async () => {
    setLoading(true);
    
    try {
      await authService.logout();
    } catch (err) {
      console.error("Logout failed", err);
    } finally {
      localStorage.removeItem('token');
      setUser(null);
      setLoading(false);
    }
  };

  const value = {
    user,
    loading,
    error,
    login,
    register,
    logout,
    isAuthenticated: !!user,
  };

  return (
    <AuthContext.Provider value={value}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
}
