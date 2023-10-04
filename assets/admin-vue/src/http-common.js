import axios from 'axios';

export const HTTP = axios.create({
  baseURL: `https://busy-squids-tan.loca.lt/`,
})
