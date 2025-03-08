import { defineStore } from 'pinia';
import axios from 'axios' 

export const usefeaturesStore = defineStore('features', {
    state: () => ({
        features: [],
        id_feature_current: null,
        feature: null,
        error_message: null,
    }),
    getters: {
    },
    actions:
    {
        async fetchFeatures() {
            if(this.features.length > 0)
            { 
              console.log('Fetching features: already loaded');
              return this.features
            }
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
        },
        async fetchFeatureDetail(decodedID) {
          if(this.feature !== null && this.id_feature_current === decodedID)
          {
            console.log('Fetching feature detail: already loaded');
            return this.feature
          }
          this.error_message = null;
          try {
              const response = await axios.get(`/api/load_features/${decodedID}`,
                  {
                      headers: {
                          'Accept': 'application/json',
                          'Content-Type': 'application/json',
                          'X-Requested-With': 'XMLHttpRequest'
                      }
                  }
              )
              if(response.data.success) {
                  this.feature = response.data.data
                  this.id_feature_current = decodedID
              }
              else {
                  this.error_message = response.data.message
              }
          } catch (error) {
              this.error_message = 'Không thể kết nối đến máy chủ'
          }
        },
    },
    persist: false
})

