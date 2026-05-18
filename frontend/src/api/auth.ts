export const saveToken = (token: string) => localStorage.setItem('jwt', token);
export const getToken = () => localStorage.getItem('jwt');
export const removeToken = () => localStorage.removeItem('jwt');
