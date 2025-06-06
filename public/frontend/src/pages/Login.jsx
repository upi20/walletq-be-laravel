import { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useAuth } from '@/contexts/AuthContext';
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Alert, AlertDescription } from "@/components/ui/alert";
import { BanknotesIcon } from '@heroicons/react/24/solid';
import { ExclamationTriangleIcon } from '@heroicons/react/24/outline';

export default function Login() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  
  const navigate = useNavigate();
  const { login } = useAuth();

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);
    
    try {
      const result = await login(email, password);
      if (result.success) {
        navigate('/dashboard');
      } else {
        setError(result.message || 'Login gagal');
      }
    } catch (err) {
      setError('Terjadi kesalahan saat login');
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="container relative min-h-screen flex flex-col items-center justify-center">
      <div className="lg:p-8 w-full max-w-md mx-auto">
        <Card className="border-none shadow-lg">
          <CardHeader className="space-y-2 text-center pb-8">
            <div className="mx-auto w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center">
              <BanknotesIcon className="h-8 w-8 text-primary" aria-hidden="true" />
            </div>
            <CardTitle className="text-2xl font-semibold">UangKu</CardTitle>
            <CardDescription>
              Kelola keuangan pribadi dengan mudah
            </CardDescription>
          </CardHeader>
          <CardContent className="grid gap-6">
            {error && (
              <Alert variant="destructive" className="animate-in fade-in-0 zoom-in-95 duration-300">
                <ExclamationTriangleIcon className="h-4 w-4" />
                <AlertDescription>{error}</AlertDescription>
              </Alert>
            )}
            <form onSubmit={handleSubmit} className="grid gap-4">
              <div className="grid gap-2">
                <Label htmlFor="email">Email</Label>
                <Input
                  id="email"
                  type="email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  placeholder="nama@email.com"
                  required
                  autoComplete="email"
                  className="transition-colors duration-200"
                />
              </div>
              <div className="grid gap-2">
                <div className="flex items-center justify-between">
                  <Label htmlFor="password">Password</Label>
                  <Link 
                    to="/forgot-password" 
                    className="text-sm font-medium text-primary hover:underline underline-offset-4"
                  >
                    Lupa password?
                  </Link>
                </div>
                <Input
                  id="password"
                  type="password"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  placeholder="••••••••"
                  required
                  autoComplete="current-password"
                  className="transition-colors duration-200"
                />
              </div>
              <Button 
                type="submit" 
                className="mt-2"
                disabled={loading}
              >
                {loading ? (
                  <div className="flex items-center gap-2">
                    <svg 
                      className="animate-spin h-4 w-4" 
                      xmlns="http://www.w3.org/2000/svg" 
                      fill="none" 
                      viewBox="0 0 24 24"
                    >
                      <circle 
                        className="opacity-25" 
                        cx="12" cy="12" r="10" 
                        stroke="currentColor" 
                        strokeWidth="4"
                      />
                      <path 
                        className="opacity-75" 
                        fill="currentColor" 
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                      />
                    </svg>
                    <span>Memuat...</span>
                  </div>
                ) : "Masuk"}
              </Button>
            </form>
          </CardContent>
          <CardFooter className="flex flex-col items-center gap-4 border-t p-6">
            <div className="text-sm text-muted-foreground text-center">
              Belum punya akun?{' '}
              <Link to="/register" className="font-medium text-primary hover:underline underline-offset-4">
                Daftar di sini
              </Link>
            </div>
          </CardFooter>
        </Card>
        <div className="mt-8 text-center text-xs text-muted-foreground">
          &copy; {new Date().getFullYear()} UangKu. Semua hak dilindungi.
        </div>
      </div>
    </div>
  );
}
