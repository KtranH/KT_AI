import { defineStore } from 'pinia';
import axios from 'axios' 

export const usefeaturesStore = defineStore('features', {
    state: () => ({
        features: [],
        error_message: null,
    }),
    actions:
    {
        async fetchFeatures() {
            if(this.features.length > 0) return
            this.error_message = null;
            
            try {
              const response = await axios.get('/api/load_features', {
                headers: {
                  'Accept': 'application/json',
                  'Content-Type': 'application/json',
                  'X-Requested-With': 'XMLHttpRequest'
                }
              });
      
              if (response.data && response.data.success) {
                this.features = response.data.data;
              } else {
                this.error_message = 'Failed to fetch features'
                console.error('Error:', response.statusText)
              }
            } catch (error) {
              console.error('Error fetching features:', error)
              this.error_message = 'An error occurred'
            }
        }
    }
})